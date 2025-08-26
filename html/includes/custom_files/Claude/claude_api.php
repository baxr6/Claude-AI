<?php
/**
 * Claude API Class for PHP-Nuke Evo Extreme
 *
 * Handles communication with Anthropic's Claude API, including sending messages,
 * configuration management, content moderation, usage statistics, and logging.
 *
 * Requires PHP 8.1 or higher.
 *
 * @category Claude_AI
 * @package  Evo-Extreme
 * @author   Deano Welch <deano.welch@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/baxr6/
 * @since    1.3.0
 */

declare(strict_types=1);

namespace CustomFiles\Claude;

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    exit('Access Denied');
}
define_once('CLAUDE_CSS', 'includes/custom_files/Claude/css/');
define_once('CLAUDE_VERSION', 'v1.2.0');

/**
 * Claude AI Class
 */
class ClaudeAPI
{
    /**
     * Claude API key
     * 
     * @var string|null apiKey
     */
    private ?string $apiKey = null;

    /**
     * Claude API base URL
     * 
     * @var string baseUrl
     */
    private string $baseUrl = 'https://api.anthropic.com/v1/messages';

    /**
     * Claude model
     * 
     * @var string model
     */
    private string $model = 'claude-3-sonnet-20240229';

    /**
     * Maximum tokens per request
     * 
     * @var int maxTokens
     */
    private int $maxTokens = 1000;

    /**
     * Temperature setting for model
     * 
     * @var float temperature
     */
    private float $temperature = 0.7;

    /**
     * Maximum Request Per Hour
     * 
     * @var int Maximum requests per hour
     */
    private int $rateLimitPerHour = 33;

    /**
     * ClaudeAPI constructor.
     */
    public function __construct()
    {
        $this->_loadConfig();
    }

    /**
     * Load configuration from database.
     *
     * Loads the Claude API configuration from the database.
     *
     * @return void
     */
    private function _loadConfig(): void
    {
        global $db, $prefix;

        $sql = "SELECT config_name, config_value FROM " . $prefix . "_claude_config";
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result)) {
            switch ($row['config_name']) {
            case 'api_key':
                    $this->apiKey = $row['config_value'];
                break;
            case 'model':
                    $this->model = $row['config_value'];
                break;
            case 'max_tokens':
                    $this->maxTokens = (int) $row['config_value'];
                break;
            case 'temperature':
                    $this->temperature = (float) $row['config_value'];
                break;
            case 'rate_limit_per_hour':
                    $this->rateLimitPerHour = (int) $row['config_value'];
                break;
            }
        }
    }

    /**
     * Send a message to Claude API.
     *
     * @param string $message User message
     * @param array  $context Optional conversation context
     *
     * @return array Response or error
     */
    public function sendMessage(string $message, array $context = []): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'Claude API key not configured'];
        }

        $messages = [];

        foreach ($context as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $data = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'messages' => $messages,
        ];

        return $this->_makeApiRequest($data);
    }

    /**
     * Make API request to Claude.
     *
     * @param array $data Request payload
     *
     * @return array Response or error
     */
    private function _makeApiRequest(array $data): array
    {
        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'PHP-Nuke-Evo-Claude-Module/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->_logError('cURL Error: ' . $error);
            return ['error' => 'Network error occurred'];
        }

        if ($httpCode !== 200) {
            $this->_logError('HTTP Error: ' . $httpCode . ' - ' . $response);
            return ['error' => 'API request failed with code: ' . $httpCode];
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->_logError('JSON Decode Error: ' . json_last_error_msg());
            return ['error' => 'Invalid response format'];
        }

        if (isset($decoded['error'])) {
            $this->_logError('API Error: ' . $decoded['error']['message']);
            return ['error' => $decoded['error']['message']];
        }

        if (empty($decoded['content'])) {
            $this->_logError('No content in API response');
            return ['error' => 'Empty response from Claude'];
        }

        $content = '';
        foreach ($decoded['content'] as $block) {
            if ($block['type'] === 'text') {
                $content .= $block['text'];
            }
        }

        return [
            'content' => $content,
            'usage' => $decoded['usage'] ?? null,
            'model' => $decoded['model'] ?? $this->model,
        ];
    }

    /**
     * Log an error to database and PHP error log.
     *
     * @param string $message Error message
     */
    private function _logError(string $message): void
    {
        global $db, $prefix;

        $timestamp = time();
        $sql = "INSERT INTO {$prefix}_claude_logs (log_type, message, timestamp) 
                VALUES ('error', '" . addslashes($message) . "', '$timestamp')";
        $db->sql_query($sql);

        if (function_exists('error_log')) {
            error_log('Claude API Error: ' . $message);
        }
    }

    /**
     * Test API connection.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        $testMessage = "Hello, please respond with 'Connection successful' to test the API.";
        $response = $this->sendMessage($testMessage);

        return !isset($response['error']);
    }

    /**
     * Get usage statistics.
     *
     * @param int|null $userId User ID to filter
     * @param int      $days   Number of days to look back
     *
     * @return array Statistics data
     */
    public function getUsageStats(?int $userId = null, int $days = 30): array
    {
        global $db, $prefix;

        $whereClause = $userId ? " AND user_id = '$userId'" : '';
        $timestampLimit = time() - ($days * 24 * 60 * 60);

        $sql = "SELECT COUNT(*) as message_count, 
                       COUNT(DISTINCT user_id) as unique_users
                FROM {$prefix}_claude_chat 
                WHERE timestamp > '$timestampLimit' $whereClause";

        $result = $db->sql_query($sql);
        return $db->sql_fetchrow($result) ?: ['message_count' => 0, 'unique_users' => 0];
    }

    /**
     * Set a configuration value.
     *
     * @param string $key   Configuration key
     * @param mixed  $value Configuration value
     *
     * @return bool|mixed
     */
    public function setConfig(string $key, mixed $value)
    {
        global $db, $prefix;

        $sql = "INSERT INTO {$prefix}_claude_config (config_name, config_value) 
                VALUES ('" . addslashes($key) . "', '" . addslashes((string)$value) . "')
                ON DUPLICATE KEY UPDATE config_value = '" . addslashes((string)$value) . "'";

        $result = $db->sql_query($sql);
        $this->_loadConfig();

        return $result;
    }

    /**
     * Get a configuration value.
     *
     * @param string $key Configuration key
     *
     * @return mixed
     */
    public function getConfig(string $key): mixed
    {
        return match ($key) {
            'api_key' => substr($this->apiKey ?? '', 0, 8) . '...',
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'rate_limit_per_hour' => $this->rateLimitPerHour,
            default => null,
        };
    }

    /**
     * Simple content moderation check.
     *
     * @param string $content Content to check
     *
     * @return bool True if content passes moderation, false otherwise
     */
    public function moderateContent(string $content): bool
    {
        $blockedWords = ['spam', 'abuse', 'hack', 'exploit'];

        foreach ($blockedWords as $word) {
            if (stripos($content, $word) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user is within rate limit.
     *
     * @param int $userId       User ID
     * @param int $limitPerHour Max messages per hour
     *
     * @return bool
     */
    public function rateLimitCheck(int $userId, int $limitPerHour = 60): bool
    {
        global $db, $prefix;

        $hourAgo = time() - 3600;

        $sql = "SELECT COUNT(*) as message_count 
                FROM {$prefix}_claude_chat 
                WHERE user_id = '$userId' AND timestamp > '$hourAgo'";

        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        return ($row['message_count'] ?? 0) < $limitPerHour;
    }
}
