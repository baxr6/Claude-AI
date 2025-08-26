<?php
/**
 * Claude Module Index Evo Extreme
 *
 * Handles communication with Anthropic's Claude API, including sending messages,
 * managing rate limits, content moderation, and usage statistics.
 *
 * @category Claude_AI
 * @package  Evo-Extreme
 * @author   Deano Welch <deano.welch@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/baxr6/
 * @since    1.3.0
 * @requires PHP 8.4 or higher
 */

if (!defined('MODULE_FILE')) {
    die("You can't access this file directly...");
}

use CustomFiles\Claude\ClaudeAPI;

$module_name = basename(dirname(__FILE__));

define_once('_CLAUDE_CSS', 'modules/'.$module_name.'/includes/css/');

// Load language file
if (file_exists(NUKE_MODULES_DIR . $module_name . '/language/lang-' . $currentlang . '.php')) {
    include_once NUKE_MODULES_DIR
        . $module_name
        . '/language/lang-'
        . $currentlang
        . '.php';
} else {
    include_once NUKE_MODULES_DIR 
        . $module_name 
        . '/language/lang-english.php';
}

// Security check for Evo
global $user, $cookie, $userinfo, $db, $prefix;

// Check if user is logged in
if (!is_user()) {
    redirect('modules.php?name=Your_Account');
    exit;
}

// Include the case file from Evolution Extreme admin structure (removed './')
require_once NUKE_INCLUDE_DIR . 'custom_files/Claude/claude_api.php';

// Rate limiting configuration
if (!defined('CLAUDE_RATE_LIMIT')) {
    define('CLAUDE_RATE_LIMIT', 30); // messages per hour per user
}
if (!defined('CLAUDE_MESSAGE_MAX_LENGTH')) {
    define('CLAUDE_MESSAGE_MAX_LENGTH', 4000); // maximum message length
}
if (!defined('CLAUDE_CONTEXT_LIMIT')) {
    define('CLAUDE_CONTEXT_LIMIT', 10); // conversation context limit
}

// Initialize variables with strict validation
$op = '';
if (!empty($_POST['op']) && is_string($_POST['op'])) {
    $op = preg_replace('/[^a-z_]/', '', $_POST['op']);
} elseif (!empty($_GET['op']) && is_string($_GET['op'])) {
    $op = preg_replace('/[^a-z_]/', '', $_GET['op']);
}

// CSRF Protection
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['claude_csrf_token'])) {
    $_SESSION['claude_csrf_token'] = bin2hex(random_bytes(32));
}

switch ($op) {
case 'send_message':
        verify_csrf_token();
        send_claude_message();
    break;
case 'clear_history':
        verify_csrf_token();
        clear_chat_history();
    break;
case 'get_history':
        verify_csrf_token();
        get_chat_history();
    break;
default:
        claude_chat_main();
    break;
}
/**
 * Database Value Escaping and Sanitization
 * 
 * Safely escapes database values for SQL queries with multiple fallback methods.
 * Handles null values, integers, and strings with 
 * proper type detection and escaping.
 * Uses database-specific escaping when available,
 * falls back to addslashes or manual escaping.
 * 
 * @param mixed $value The value to escape (null, int, string, or other types)
 * 
 * @return string The escaped value ready for SQL insertion, 
 * including quotes for strings
 * 
 * @global object $db Database connection object with 
 * optional sql_escape_string method
 * 
 * Security Features:
 * - NULL value handling prevents SQL injection
 * - Integer validation using ctype_digit for safety
 * - Prefers database-native escaping methods
 * - Manual character escaping as last resort
 * - Handles backslashes, quotes, and null bytes
 * 
 * Usage Examples:
 * - nukeDBEscape(null) returns 'NULL'
 * - nukeDBEscape(123) returns '123'
 * - nukeDBEscape("O'Reilly") returns "'O\'Reilly'" (escaped)
 */
function nukeDBEscape($value)
{
    global $db;
    if (is_null($value)) {
        return 'NULL';
    }
    if (is_int($value) || ctype_digit((string)$value)) {
        return (string)intval($value);
    }
    // Prefer wrapper escape if available
    if (method_exists($db, 'sql_escape_string')) {
        return "'" . $db->sql_escape_string($value) . "'";
    }
    if (function_exists('addslashes')) {
        return "'" . addslashes($value) . "'";
    }
    // Last resort
    return "'" . str_replace(["\\", "'", "\0"], ["\\\\", "\\'", ''], $value) . "'";
}

/**
 * JSON Response Header Configuration
 * 
 * Sets appropriate HTTP headers for JSON API responses including content type,
 * character encoding, and cache control directives to prevent caching 
 * of dynamic content.
 * Should be called before outputting any JSON response data.
 * 
 * @return void
 * 
 * Headers Set:
 * - Content-Type: application/json with UTF-8 encoding
 * - Cache-Control: Comprehensive no-cache directives
 * 
 * Cache Control Features:
 * - no-store: Prevents storage in any cache
 * - no-cache: Forces revalidation with server
 * - must-revalidate: Requires fresh response when stale
 * - max-age=0: Sets immediate expiration
 * 
 * Usage: Call before json_encode() output in API endpoints
 */
function json_headers()
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
}

/**
 * CSRF Token Verification for API Requests
 * 
 * Validates CSRF tokens from POST requests against session-stored values using
 * timing-safe comparison. Automatically sends JSON error response and terminates
 * execution if verification fails. Sets JSON headers for consistent API responses.
 * 
 * @return void
 * @throws never Returns - function exits on token mismatch
 * 
 * Security Features:
 * - Timing-safe hash comparison prevents timing attacks
 * - Automatic HTTP 403 response on failure
 * - Session-based token storage and validation
 * - JSON error response format
 * - Immediate script termination on failure
 * 
 * Prerequisites:
 * - $_SESSION['claude_csrf_token'] must be set
 * - $_POST['csrf_token'] must be provided
 * - Session must be active
 * 
 * Error Response: {"error": "Invalid CSRF token"} with HTTP 403
 */
function verify_csrf_token()
{
    json_headers();
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['claude_csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}

/**
 * User Rate Limiting Validation
 * 
 * Checks if a user has exceeded the hourly message rate limit by counting
 * user messages sent within the last hour. Uses fail-open strategy on database
 * errors to prevent user lockout during system issues.
 * 
 * @param int|string $user_id The user ID to check rate limits for
 * 
 * @return bool True if under rate limit, false if limit exceeded
 * 
 * @global object $db Database connection for query execution
 * @global string $prefix Database table prefix for queries
 * 
 * Rate Limiting Logic:
 * - Counts messages from last 3600 seconds (1 hour)
 * - Only counts user-sent messages (sender = 'user')
 * - Compares against CLAUDE_RATE_LIMIT constant
 * - Fails open on database errors for availability
 * 
 * Database Requirements:
 * - Table: {prefix}_claude_chat
 * - Columns: user_id, sender, timestamp
 * - Timestamp should be Unix timestamp format
 * 
 * Constants Required:
 * - CLAUDE_RATE_LIMIT: Maximum messages per hour
 */
function check_rate_limit($user_id)
{
    global $db, $prefix;
    $user_id = (int) $user_id;
    $one_hour_ago = time() - 3600;

    $sql = "SELECT COUNT(*) AS message_count FROM {$prefix}_claude_chat 
    WHERE user_id = $user_id 
    AND sender = 'user' 
    AND timestamp > $one_hour_ago";
    $res = $db->sql_query($sql);
    if (!$res) {
        return true; // fail-open on DB hiccup to avoid lockout
    }
    $row = $db->sql_fetchrow($res);
    $count = isset($row['message_count']) ? (int)$row['message_count'] : 0;
    return ($count < CLAUDE_RATE_LIMIT);
}

/**
 * Message Content Validation and Security Scanning
 * 
 * Validates message content for type safety, encoding, 
 * length limits, and security threats.
 * Performs comprehensive scanning for malicious patterns including script injection,
 * iframe embedding, and various XSS attack vectors.
 * 
 * @param mixed $message The message content to validate
 * 
 * @return bool True if message passes all validation checks, false otherwise
 * 
 * Validation Checks:
 * - Type validation (must be string)
 * - UTF-8 encoding validation and conversion
 * - Length limit enforcement (CLAUDE_MESSAGE_MAX_LENGTH)
 * - XSS pattern detection and blocking
 * - Script tag injection prevention
 * - Iframe embedding protection
 * - JavaScript/VBScript URI scheme blocking
 * - Event handler attribute detection
 * 
 * Security Patterns Blocked:
 * - <script> tags with content
 * - <iframe> tags with content  
 * - javascript: URI schemes
 * - data:text/html URI schemes
 * - vbscript: URI schemes
 * - HTML event handlers (onclick, onload, etc.)
 * 
 * Constants Required:
 * - CLAUDE_MESSAGE_MAX_LENGTH: Maximum allowed message length
 */
function validate_message($message)
{
    if ($message === null || !is_string($message)) return false;

    // Ensure UTF-8 early so mb_* works as expected
    if (!mb_check_encoding($message, 'UTF-8')) {
        $message = mb_convert_encoding($message, 'UTF-8', 'auto');
    }

    if (mb_strlen($message, 'UTF-8') > CLAUDE_MESSAGE_MAX_LENGTH) {
        return false;
    }

    // Basic content validation - block potentially malicious patterns
    $dangerous_patterns = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/<iframe\b[^>]*>.*?<\/iframe>/mi',
        '/\bjavascript\s*:/i',
        '/\bdata\s*:\s*text\/html/i',
        '/\bvbscript\s*:/i',
        '/\son\w+\s*=\s*/i'
    ];
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $message)) return false;
    }
    return true;
}

/**
 * Message Content Sanitization and Normalization
 * 
 * Sanitizes and normalizes message content by removing dangerous characters,
 * ensuring proper UTF-8 encoding, and normalizing whitespace. Prepares content
 * for safe storage and display while preserving legitimate content.
 * 
 * @param string $message The message content to sanitize
 * 
 * @return string The sanitized and normalized message content
 * 
 * Sanitization Steps:
 * - Null byte removal (prevents injection attacks)
 * - UTF-8 encoding validation and conversion
 * - Leading/trailing whitespace removal
 * - Excessive whitespace normalization
 * - Multi-space collapse to single spaces
 * 
 * Character Encoding:
 * - Validates UTF-8 encoding integrity
 * - Auto-detects and converts other encodings
 * - Ensures consistent character representation
 * 
 * Whitespace Handling:
 * - Removes leading and trailing spaces
 * - Collapses multiple consecutive spaces
 * - Normalizes various whitespace characters
 * - Preserves single spaces between words
 * 
 * Security Features:
 * - Null byte injection prevention
 * - Encoding attack mitigation
 * - Content length optimization
 */
function sanitize_message($message)
{
    // Remove null bytes
    $message = str_replace("\0", '', $message);

    // Convert to UTF-8 if needed
    if (!mb_check_encoding($message, 'UTF-8')) {
        $message = mb_convert_encoding($message, 'UTF-8', 'auto');
    }

    // Trim whitespace and collapse excessive whitespace
    $message = trim($message);
    $message = preg_replace('/\s+/', ' ', $message);

    return $message;
}

/**
 * Claude AI Chat Interface Main Controller
 * 
 * Renders the complete Claude AI chat interface including message history,
 * input controls, security features, and asset loading. Provides a full-featured
 * chat experience with CSRF protection, rate limiting, and responsive design.
 * 
 * @return void
 * 
 * @global array $ThemeSel Current theme selection for styling
 * @global array $userinfo Current user information and authentication
 * @global string $module_name Current module name for navigation
 * 
 * Interface Components:
 * - Chat header with title and controls
 * - Scrollable message history container
 * - Message input form with validation
 * - Clear chat functionality
 * - Real-time message loading
 * - Character count limitations
 * 
 * Security Features:
 * - CSRF token protection for all forms
 * - HTML entity encoding for XSS prevention
 * - Message length validation and limits
 * - User authentication requirements
 * - Session-based security tokens
 * 
 * User Experience Features:
 * - Welcome message personalization
 * - Responsive design and styling
 * - Real-time chat functionality
 * - Message timestamp display
 * - Input validation feedback
 * - Character limit indicators
 * 
 * Dependencies:
 * - Active user session ($_SESSION)
 * - Chat history loading function
 * - Asset inclusion functions
 * - Theme system integration
 * - Language constants for i18n
 * 
 * Constants Required:
 * - _CLAUDE_CSS: CSS file path
 * - CLAUDE_VERSION: Version for cache busting
 * - CLAUDE_MESSAGE_MAX_LENGTH: Input length limit
 * - Language constants for interface text
 * 
 * Global Functions Called:
 * - evo_include_style(): CSS loading
 * - title(): Page title setting
 * - OpenTable()/CloseTable(): Layout functions
 * - load_chat_history(): Message history loading
 * - include_chat_assets(): JavaScript/CSS inclusion
 */
function claude_chat_main()
{
    global $ThemeSel, $userinfo, $module_name;
    // Include CSS and JavaScript
    include_chat_assets();

    $user_id = intval($userinfo['user_id']);
    $username = htmlspecialchars($userinfo['username'], ENT_QUOTES, 'UTF-8');
    $csrf_token = isset($_SESSION['claude_csrf_token']) 
    ? $_SESSION['claude_csrf_token'] 
    : '';
    evo_include_style('claude-style', CLAUDE_CSS . 'claude.css', CLAUDE_VERSION);
    include NUKE_BASE_DIR . 'header.php';
    title(_CLAUDE_TITLE);
    OpenTable();

    echo '<div class="claude-chat-container">';
    echo '<div class="claude-header">';
    echo '<h2>' . htmlspecialchars(_CLAUDE_TITLE, ENT_QUOTES, 'UTF-8') . '</h2>';
    echo '<div class="chat-controls">';
    echo '<button id="clear-chat" class="btn btn-warning btn-sm">' . 
          htmlspecialchars(_CLAUDE_CLEAR, ENT_QUOTES, 'UTF-8') . '</button>';
    echo '</div>';
    echo '</div>';

    // Chat messages container
    echo '<div id="chat-messages" class="chat-messages-container">';
    echo '<div class="welcome-message">';
    echo '<div class="message claude-message">';
    echo '<div class="message-content">' . sprintf(
        htmlspecialchars(_CLAUDE_WELCOME, ENT_QUOTES, 'UTF-8'), $username
    ) . '</div>';
    echo '<div class="message-time">' . htmlspecialchars(
        date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8'
    ) . '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Load existing chat history (server outputs JSON blob; JS escapes on render)
    load_chat_history($user_id);

    // Chat input form with CSRF protection
    echo '<div class="chat-input-container">';
    echo '<form id="chat-form" method="POST">';
    echo '<input type="hidden" name="op" value="send_message">';
    echo '<input type="hidden" name="csrf_token" value="' . 
    htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    echo '<div class="input-group">';
    echo '<textarea id="message-input" name="message" class="form-control" placeholder="' . htmlspecialchars(_CLAUDE_INPUT_PLACEHOLDER, ENT_QUOTES, 'UTF-8') . '" rows="3" required maxlength="' . CLAUDE_MESSAGE_MAX_LENGTH . '"></textarea>';
    echo '<div class="input-group-append">';
    echo '<button type="submit" id="send-btn" class="btn btn-primary">' . 
    htmlspecialchars(_CLAUDE_SEND_BUTTON, ENT_QUOTES, 'UTF-8') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '<small class="text-muted">Maximum ' . 
    CLAUDE_MESSAGE_MAX_LENGTH . ' characters</small>';
    echo '</form>';
    echo '</div>';
    echo '</div>';


    CloseTable();
    include NUKE_BASE_DIR . 'footer.php';
}

function send_claude_message(): void
{
    global $userinfo, $db, $prefix;

    json_headers();

    if (!is_user()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }

    $userId = intval($userinfo['user_id']);

    // Rate limit check
    if (!check_rate_limit($userId)) {
        http_response_code(429);
        echo json_encode([
            'error' => 'Rate limit exceeded. Wait before sending another message.'
        ]);
        exit;
    }

    // Validate POST message
    if (empty($_POST['message'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is required']);
        exit;
    }

    $rawMessage = (string)$_POST['message'];

    if (!validate_message($rawMessage)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid message content or length']);
        exit;
    }

    $userMessage = sanitize_message($rawMessage);

    if ($userMessage === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Message cannot be empty']);
        exit;
    }

    // Save user message
    $timestamp = time();
    $sql = "INSERT INTO {$prefix}_claude_chat 
            (user_id, message, sender, timestamp) 
            VALUES (" . intval($userId) . ", " . nukeDBEscape($userMessage) . ", 'user', " . intval($timestamp) . ")";
    if (!$db->sql_query($sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save message']);
        exit;
    }

    // Send message to Claude
    try {
        $claude = new ClaudeAPI();

        // Get conversation context
        $context = get_conversation_context($userId, CLAUDE_CONTEXT_LIMIT);

        $claudeResponse = $claude->sendMessage($userMessage, $context);

    } catch (Exception $e) {
        error_log("Claude API exception: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    // Handle Claude response
    if ($claudeResponse && !isset($claudeResponse['error'])) {
        $claudeMessage = sanitize_message($claudeResponse['content'] ?? '');

        if ($claudeMessage !== '') {
            $sql = "INSERT INTO {$prefix}_claude_chat 
                    (user_id, message, sender, timestamp) 
                    VALUES (" . intval($userId) . ", " . nukeDBEscape($claudeMessage) . ", 'claude', " . intval($timestamp) . ")";
            $db->sql_query($sql);
        }

        echo json_encode([
            'success' => true,
            'user_message' => $userMessage,
            'claude_response' => $claudeMessage,
            'timestamp' => date('Y-m-d H:i:s', $timestamp),
        ]);
    } else {
        $errorMsg = $claudeResponse['error'] ?? 'Unknown API error';
        error_log("Claude API error: " . $errorMsg);
        http_response_code(503);
        echo json_encode(['error' => $errorMsg]);
    }

    exit;
}


function clear_chat_history()
{
    global $userinfo, $db, $prefix;

    json_headers();

    if (!is_user()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $user_id = intval($userinfo['user_id']);

    $sql = "DELETE FROM {$prefix}_claude_chat WHERE user_id = " . intval($user_id);
    $result = $db->sql_query($sql);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to clear history']);
    }
    exit;
}

function get_chat_history()
{
    global $userinfo;

    json_headers();

    if (!is_user()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $user_id = intval($userinfo['user_id']);
    $history = load_chat_history_array($user_id);
    echo json_encode(['history' => $history]);
    exit;
}

function load_chat_history($user_id)
{
    global $db, $prefix;

    $user_id = intval($user_id);
    $sql = "SELECT message, sender, timestamp FROM {$prefix}_claude_chat WHERE user_id = $user_id ORDER BY timestamp ASC LIMIT 50";
    $result = $db->sql_query($sql);

    if ($result && $db->sql_numrows($result) > 0) {
        $messages = [];
        while ($row = $db->sql_fetchrow($result)) {
            $messages[] = [
                'message'   => (string)$row['message'], // RAW
                'sender'    => (string)$row['sender'],
                'timestamp' => date('Y-m-d H:i:s', (int)$row['timestamp'])
            ];
        }
        echo '<script>';
        echo 'var chatHistory = ' . json_encode($messages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';';
        echo 'loadChatHistory(chatHistory);';
        echo '</script>';
    }
}

function load_chat_history_array($user_id)
{
    global $db, $prefix;

    $user_id = intval($user_id);
    $sql = "SELECT message, sender, timestamp FROM {$prefix}_claude_chat WHERE user_id = $user_id ORDER BY timestamp ASC LIMIT 50";
    $result = $db->sql_query($sql);

    $messages = [];
    if ($result) {
        while ($row = $db->sql_fetchrow($result)) {
            $messages[] = [
                'message'   => (string)$row['message'], // RAW
                'sender'    => (string)$row['sender'],
                'timestamp' => date('Y-m-d H:i:s', (int)$row['timestamp'])
            ];
        }
    }
    return $messages;
}

function get_conversation_context($user_id, $limit = 10)
{
    global $db, $prefix;

    $user_id = intval($user_id);
    $limit = max(1, intval($limit));

    $sql = "SELECT message, sender FROM {$prefix}_claude_chat WHERE user_id = $user_id ORDER BY timestamp DESC LIMIT $limit";
    $result = $db->sql_query($sql);

    $context = [];
    if ($result) {
        while ($row = $db->sql_fetchrow($result)) {
            $context[] = [
                'role'    => ($row['sender'] === 'user' ? 'user' : 'assistant'),
                'content' => (string)$row['message'] // RAW to the model
            ];
        }
    }

    return array_reverse($context);
}

function include_chat_assets()
{
    global $module_name;

    $csrf_token = htmlspecialchars($_SESSION['claude_csrf_token'], ENT_QUOTES, 'UTF-8');
    $send_button_text = htmlspecialchars(_CLAUDE_SEND_BUTTON, ENT_QUOTES, 'UTF-8');
    $clear_confirm_text = htmlspecialchars(_CLAUDE_CLEAR_CONFIRM, ENT_QUOTES, 'UTF-8');
    $history_cleared_text = htmlspecialchars(_CLAUDE_HISTORY_CLEARED, ENT_QUOTES, 'UTF-8');
    $max_length = CLAUDE_MESSAGE_MAX_LENGTH;

    echo '<script>
    function loadChatHistory(messages) {
        var container = document.getElementById(\'chat-messages\');
        var welcomeMsg = container.querySelector(\'.welcome-message\');

        messages.forEach(function(msg) {
            var messageDiv = document.createElement(\'div\');
            messageDiv.className = \'message \' + (msg.sender === \'user\' ? \'user-message\' : \'claude-message\');
            messageDiv.innerHTML = \'<div class="message-content">\' + escapeHtml(String(msg.message)) + \'</div>\' +
                                   \'<div class="message-time">\' + String(msg.timestamp) + \'</div>\';
            container.insertBefore(messageDiv, welcomeMsg ? welcomeMsg.nextSibling : null);
        });

        container.scrollTop = container.scrollHeight;
    }

    document.addEventListener(\'DOMContentLoaded\', function() {
        var form = document.getElementById(\'chat-form\');
        var messageInput = document.getElementById(\'message-input\');
        var sendBtn = document.getElementById(\'send-btn\');
        var clearBtn = document.getElementById(\'clear-chat\');
        var messagesContainer = document.getElementById(\'chat-messages\');

        var charCount = document.createElement(\'small\');
        charCount.className = \'text-muted char-counter\';
        charCount.style.float = \'right\';
        messageInput.parentNode.appendChild(charCount);

        function updateCharCount() {
            var remaining = ' . $max_length . ' - messageInput.value.length;
            charCount.textContent = remaining + \' characters remaining\';
            charCount.style.color = remaining < 100 ? \'#dc3545\' : \'#6c757d\';
        }

        messageInput.addEventListener(\'input\', function() {
            this.style.height = \'auto\';
            this.style.height = Math.min(this.scrollHeight, 120) + \'px\';
            updateCharCount();
            if (this.value.length > ' . $max_length . ') {
                this.value = this.value.substring(0, ' . $max_length . ');
                updateCharCount();
            }
        });

        updateCharCount();

        form.addEventListener(\'submit\', function(e) {
            e.preventDefault();
            var message = messageInput.value.trim();
            if (!message || sendBtn.disabled || message.length > ' . $max_length . ') return;
            sendBtn.disabled = true;
            sendBtn.textContent = \'Sending...\';
            addMessageToChat(message, \'user\');
            messageInput.value = \'\';
            messageInput.style.height = \'auto\';
            updateCharCount();

            var formData = new FormData();
            formData.append(\'op\', \'send_message\');
            formData.append(\'message\', message);
            formData.append(\'csrf_token\', \'' . $csrf_token . '\');

            fetch(\'modules.php?name=' . $module_name . '\', { method: \'POST\', body: formData })
            .then(function(response) {
                if (!response.ok) {
                    return response.json().then(function(data){ throw new Error(data.error || \'Request failed\'); });
                }
                return response.json();
            })
            .then(function(data) {
                if (data.success) addMessageToChat(data.claude_response, \'claude\');
                else addMessageToChat(\'Sorry, there was an error: \' + (data.error || \'Unknown error\'), \'claude\');
            })
            .catch(function(error) { console.error(\'Error:\', error); addMessageToChat(\'Error: \' + error.message, \'claude\'); })
            .finally(function() { sendBtn.disabled = false; sendBtn.textContent = \'' . $send_button_text . '\'; messageInput.focus(); });
        });

        clearBtn.addEventListener(\'click\', function() {
            if (confirm(\'' . $clear_confirm_text . '\')) {
                var formData = new FormData();
                formData.append(\'op\', \'clear_history\');
                formData.append(\'csrf_token\', \'' . $csrf_token . '\');
                fetch(\'modules.php?name=' . $module_name . '\', { method: \'POST\', body: formData })
                .then(function(response){ return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        messagesContainer.innerHTML = \'<div class="welcome-message"><div class="message claude-message"><div class="message-content">' . $history_cleared_text . '</div><div class="message-time">\' + new Date().toLocaleString() + \'</div></div></div>\';
                    }
                })
                .catch(function(error) { console.error(\'Error clearing history:\', error); });
            }
        });

        messageInput.addEventListener(\'keydown\', function(e) {
            if (e.key === \'Enter\' && !e.shiftKey) { e.preventDefault(); if (this.value.trim() && !sendBtn.disabled) form.dispatchEvent(new Event(\'submit\')); }
        });

        messageInput.focus();
    });

    function addMessageToChat(message, sender) {
        var container = document.getElementById(\'chat-messages\');
        var messageDiv = document.createElement(\'div\');
        var timestamp = new Date().toLocaleString();
        messageDiv.className = \'message \' + (sender === \'user\' ? \'user-message\' : \'claude-message\');
        messageDiv.innerHTML = \'<div class="message-content">\' + escapeHtml(String(message)) + \'</div><div class="message-time">\' + timestamp + \'</div>\';
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    }

    function escapeHtml(text) {
        var map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", "\\"" : "&quot;", "\'" : "&#039;" };
        return String(text).replace(/[&<>"\']/g, function(m) { return map[m]; });
    }
    </script>';
}
