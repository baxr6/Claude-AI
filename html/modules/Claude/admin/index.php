<?php
/**
 * Claude Admin Index Evo Extreme
 *
 * Handles communication with Anthropic's Claude API, including sending messages,
 * managing rate limits, content moderation, and usage statistics.
 *
 * @category Claude_AI
 * @package  Evo-Extreme
 * @author   Deano Welch <deano.welch@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/baxr6/
 * @since    1.1.1
 * @requires PHP 8.4 or higher
 */

global $prefix, $db, $admin_file;

use CustomFiles\Claude\ClaudeAPI;

if (isset($_POST['op']) && $_POST['op'] === 'test_api') {
    header('Content-Type: application/json; charset=utf-8');

    // Example API test logic
    $success = false;
    $message = '';

    try {
        // Example: simulate success
        $success = true;
    } catch (Exception $e) {
        $success = false;
        $message = $e->getMessage();
    }

    echo json_encode(
        [
        'success' => $success,
        'message' => $message
        ]
    );

    exit; // VERY IMPORTANT: stops the rest of the admin HTML from being output
}
// Include the case file from Evolution Extreme admin structure
require_once NUKE_INCLUDE_DIR . './custom_files/Claude/claude_api.php';

if (file_exists(NUKE_PLUGINS_DIR . '/jquery-ui/jquery-ui.min.css')
    && file_exists(NUKE_PLUGINS_DIR . '/jquery/jquery.min.js')
    && file_exists(NUKE_PLUGINS_DIR . '/jquery-ui/jquery-ui.min.js')
) {
    /**
     * JQuery UI
     *
     * @package jQuery UI
     * @author  OpenJS Foundation and other contributors
     * @version 1.13.2
     * @license https://github.com/jquery/jquery-ui/blob/main/LICENSE.txt
     * @link    https://jqueryui.com/
     */

    // 1. Include the core jQuery library first.
    // Assuming you have a jquery folder with the file.
    evo_include_script(
        'jquery',
        NUKE_PLUGINS_DIR . '/jquery/jquery.min.js',
        '3.7.1' // Use the correct version number
    );

    // 2. Then, include the jQuery UI library.
    evo_include_style(
        'jquery-ui-stylesheet',
        NUKE_PLUGINS_DIR . '/jquery-ui/jquery-ui.min.css',
        '1.13.2'
    );
    evo_include_script(
        'jquery-ui',
        NUKE_PLUGINS_DIR . '/jquery-ui/jquery-ui.min.js',
        '1.13.2'
    );
}

if (is_admin()) {

    evo_include_style('claude-style', CLAUDE_CSS . 'claude.css', CLAUDE_VERSION);
    include_once NUKE_BASE_DIR.'header.php';
    
    // Get operation parameter - this should be at the top
    $op = (!empty($_POST['op'])) ? $_POST['op'] : ((!empty($_GET['op'])) ? $_GET['op'] : '');

    // Handle the switch statement
    switch ($op) {
    case 'save_config':
            saveClaudeConfig();
        break;
    case 'test_api':
            testClaudeAPI();
        break;
    case 'view_logs':
            viewClaudeLogs();
        break;
    case 'clear_logs':
            clearClaudeLogs();
        break;
    case 'usage_stats':
            showUsageStats();
        break;
    case 'claude':
    default:
            claudeAdminMain();
        break;
    }
    
    include_once NUKE_BASE_DIR.'footer.php';
} else {
    include NUKE_BASE_DIR.'header.php';
    GraphicAdmin();
    OpenTable();
    echo "<center><strong>"._ERROR."</strong><br />
    <br />"._CLAUDE_ADMIN_ERROR_NO_PERMISSION."</center>";
    CloseTable();
    include NUKE_BASE_DIR.'footer.php';
}
/**
 * Claude AI Admin Interface Main Function
 * 
 * Renders the main administrative interface for Claude AI configuration,
 * including tabs for configuration, usage statistics, and logs.
 * Provides form handling for API settings and integrates with jQuery UI tabs.
 * 
 * @return void
 * @throws Exception When session operations fail
 * 
 * @global string $admin_file The admin file path for form submissions
 */
function claudeAdminMain() 
{
    global $admin_file;
    echo '<script>
    $( function() {
      $( "#claude_tabs" ).tabs({
        // Optional: Make sure the correct tab is active on page load
        // This would require more complex logic to get the current op
      });
    } );
  </script>';
    $claude = new ClaudeAPI();
    
    // Header/Title
    OpenTable();
    echo "<div id=\"tabs\" align=\"center\"><a href=\"$admin_file.php?op=claude\">" . _CLAUDE_ADMIN_TITLE . "</a></div>";
    echo "<br /><br />";
    echo "<div align=\"center\">[ <a href=\"$admin_file.php\">" . _CLAUDE_ADMIN_BACK_TO_MAIN . "</a> ]</div>";
    CloseTable();
    echo "<br />";
    title(_CLAUDE_ADMIN_TITLE);
    OpenTable();
    
    echo '<div class="status-card">';
    echo '<h3>' . _CLAUDE_ADMIN_SUBTITLE . '</h3>';
    echo '</div>';
    
    // Correct jQuery UI Tab Structure
    echo '<div id="claude_tabs" class="claude-admin-tabs">';
    echo '<ul>';
    echo '<li><a href="#claude_tabs-1">' . _CLAUDE_ADMIN_TAB_CONFIG . '</a></li>';
    echo '<li><a href="#claude_tabs-2">' . _CLAUDE_ADMIN_TAB_STATS . '</a></li>';
    echo '<li><a href="#claude_tabs-3">' . _CLAUDE_ADMIN_TAB_LOGS . '</a></li>';
    echo '</ul>';
    
    // Tab content for Configuration
    echo '<div id="claude_tabs-1">';
    echo '<div class="config-section">';
    echo '<h3>' . _CLAUDE_ADMIN_API_CONFIG . '</h3>';
    echo '<form method="post" action="' . $admin_file . '.php" onsubmit="return validateConfig()">';
    echo '<input type="hidden" name="op" value="save_config">';
    
    // Add CSRF token
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    
    echo '<table class="config-table">';
    // API Key
    echo '<tr>';
    echo '<td><label for="api_key">' . _CLAUDE_ADMIN_API_KEY . ':</label></td>';
    echo '<td>';
    $masked_key = $claude->getConfig('api_key');
    echo '<input type="password" id="api_key" name="api_key" value="" class="form-control" placeholder="' . _CLAUDE_ADMIN_API_KEY_PLACEHOLDER . '" autocomplete="new-password">';
    echo '<small>' . _CLAUDE_ADMIN_API_KEY_CURRENT . ': ' . (!empty($masked_key) ? _CLAUDE_ADMIN_API_KEY_MASKED : _CLAUDE_ADMIN_API_KEY_NOT_SET) . '</small><br>';
    echo '<small>' . _CLAUDE_ADMIN_GETAPIKEY . ' <a href="https://console.anthropic.com/" target="_blank" rel="noopener">' . _CLAUDE_ADMIN_ANTHROPIC_CONSOLE . '</a></small>';
    echo '</td>';
    echo '</tr>';
    
    // Model selection
    echo '<tr>';
    echo '<td><label for="model">' . _CLAUDE_ADMIN_MODEL . ':</label></td>';
    echo '<td>';
    echo '<select id="model" name="model" class="form-control">';
    $current_model = $claude->getConfig('model');
    $models = [
        'claude-3-haiku-20240307' => _CLAUDE_ADMIN_MODEL_HAIKU,
        'claude-3-sonnet-20240229' => _CLAUDE_ADMIN_MODEL_SONNET,
        'claude-3-opus-20240229' => _CLAUDE_ADMIN_MODEL_OPUS,
    ];
    foreach ($models as $value => $label) {
        $selected = ($current_model === $value) ? 'selected' : '';
        echo "<option value=\"$value\" $selected>$label</option>";
    }
    echo '</select>';
    echo '<small>' . _CLAUDE_ADMIN_MODEL_DESC . '</small>';
    echo '</td>';
    echo '</tr>';
    
    // Max tokens
    echo '<tr>';
    echo '<td><label for="max_tokens">' . _CLAUDE_ADMIN_MAX_TOKENS . ':</label></td>';
    echo '<td>';
    echo '<input type="number" id="max_tokens" name="max_tokens" value="' . $claude->getConfig('max_tokens') . '" min="100" max="4096" class="form-control">';
    echo '<small>' . _CLAUDE_ADMIN_MAX_TOKENS_DESC . '</small>';
    echo '</td>';
    echo '</tr>';
    
    // Temperature
    echo '<tr>';
    echo '<td><label for="temperature">' . _CLAUDE_ADMIN_TEMPERATURE . ':</label></td>';
    echo '<td>';
    echo '<input type="number" id="temperature" name="temperature" value="' . $claude->getConfig('temperature') . '" min="0" max="1" step="0.1" class="form-control">';
    echo '<small>' . _CLAUDE_ADMIN_TEMPERATURE_DESC . '</small>';
    echo '</td>';
    echo '</tr>';

    // Rate limit
    echo '<tr>';
    echo '<td><label for="rate_limit_per_hour">' . _CLAUDE_ADMIN_RATE_LIMIT . ':</label></td>';
    echo '<td>';
    echo '<input type="number" id="rate_limit_per_hour" name="rate_limit_per_hour" value="' . $claude->getConfig('rate_limit_per_hour') . '" min="30" max="100" class="form-control">';
    echo '<small>' . _CLAUDE_ADMIN_RATE_LIMIT_DESC . '</small>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    echo '<div class="config-actions">';
    echo '<button type="submit" class="btn btn-primary">' . _CLAUDE_ADMIN_SAVE_CONFIG . '</button>';
    echo '<button type="button" onclick="testAPI(this)" class="btn btn-secondary">' . _CLAUDE_ADMIN_TEST_API . '</button>';
    echo '</div>';
    
    echo '</form>';
    echo '</div>';
    
    // Module status
    showModuleStatus();
    echo '</div>'; // close claude_tabs-1
    
    // Tab content for Usage Stats
    echo '<div id="claude_tabs-2">';
    showUsageStats();
    echo '</div>'; // close claude_tabs-2
    
    // Tab content for Logs
    echo '<div id="claude_tabs-3">';
    viewClaudeLogs();
    echo '</div>'; // close claude_tabs-3
    
    echo '</div>'; // close claude_tabs
    
    // Include admin CSS and JS
    includeAdminAssets();
    
    CloseTable();
}

function saveClaudeConfig() 
{
    global $admin_file;
    
    // CSRF Protection
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die(_CLAUDE_ADMIN_ERROR_CSRF);
    }
    
    $claude = new ClaudeAPI();
    
    // Validate inputs
    if (!empty($_POST['api_key']) && strlen($_POST['api_key']) > 10) {
        if (!preg_match('/^sk-ant-[a-zA-Z0-9_-]+$/', $_POST['api_key'])) {
            header('Location: ' . $admin_file . '.php?op=claude&msg=invalid_api_key');
            exit;
        }
        $claude->setConfig('api_key', $_POST['api_key']);
    }
    
    if (!empty($_POST['model'])) {
        $allowed_models = ['claude-3-haiku-20240307', 'claude-3-sonnet-20240229', 'claude-3-opus-20240229'];
        if (in_array($_POST['model'], $allowed_models)) {
            $claude->setConfig('model', $_POST['model']);
        }
    }
    
    if (!empty($_POST['max_tokens'])) {
        $max_tokens = intval($_POST['max_tokens']);
        if ($max_tokens >= 100 && $max_tokens <= 4096) {
            $claude->setConfig('max_tokens', $max_tokens);
        }
    }
    
    if (isset($_POST['temperature'])) {
        $temperature = floatval($_POST['temperature']);
        if ($temperature >= 0 && $temperature <= 1) {
            $claude->setConfig('temperature', $temperature);
        }
    }
    
    if (!empty($_POST['rate_limit_per_hour'])) {
        $rate_limit_per_hour = intval($_POST['rate_limit_per_hour']);
        if ($rate_limit_per_hour >= 30 && $rate_limit_per_hour <= 100) {
            $claude->setConfig('rate_limit_per_hour', $rate_limit_per_hour);
        }
    }
    header('Location: ' . $admin_file . '.php?op=claude&msg=config_saved');
    exit;
}

function testClaudeAPI() {
    $claude = new ClaudeAPI();
    $result = $claude->testConnection();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

function showModuleStatus() {
    global $prefix, $db;
    echo '<div class="status-section">';
    echo '<h3>' . _CLAUDE_ADMIN_MODULE_STATUS . '</h3>';
    
    $claude = new ClaudeAPI();
    $stats = $claude->getUsageStats(null, 7);
    
    echo '<div class="status-grid">';
    
    // API Status
    echo '<div class="status-card">';
    echo '<h4>' . _CLAUDE_ADMIN_API_STATUS . '</h4>';
    $api_status = $claude->testConnection();
    echo '<div class="status-indicator ' . ($api_status ? 'status-ok' : 'status-error') . '">';
    echo $api_status ? _CLAUDE_ADMIN_STATUS_CONNECTED : _CLAUDE_ADMIN_STATUS_DISCONNECTED;
    echo '</div>';
    echo '</div>';
    
    // Message Statistics
    echo '<div class="status-card">';
    echo '<h4>' . _CLAUDE_ADMIN_MESSAGES_7_DAYS . '</h4>';
    echo '<div class="stat-number">' . intval($stats['message_count']) . '</div>';
    echo '</div>';
    
    // Active Users
    echo '<div class="status-card">';
    echo '<h4>' . _CLAUDE_ADMIN_ACTIVE_USERS_7_DAYS . '</h4>';
    echo '<div class="stat-number">' . intval($stats['unique_users']) . '</div>';
    echo '</div>';
    
    // Database Status
    echo '<div class="status-card">';
    echo '<h4>' . _CLAUDE_ADMIN_DATABASE_STATUS . '</h4>';
    $tables_exist = checkDatabaseTables();
    echo '<div class="status-indicator ' . ($tables_exist ? 'status-ok' : 'status-error') . '">';
    echo $tables_exist ? _CLAUDE_ADMIN_STATUS_TABLES_OK : _CLAUDE_ADMIN_STATUS_TABLES_MISSING;
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

function checkDatabaseTables() {
    global $prefix, $db;
    
    $required_tables = [
        $prefix . '_claude_chat',
        $prefix . '_claude_config',
        $prefix . '_claude_logs',
        $prefix . '_claude_preferences'
    ];
    
    foreach ($required_tables as $table) {
        $result = $db->sql_query("SHOW TABLES LIKE '$table'");
        if ($db->sql_numrows($result) == 0) {
            return false;
        }
    }
    
    return true;
}

function showUsageStats() {
    global $admin_file, $prefix, $db;
    OpenTable();
    
    echo '<div class="admin-header">';
    echo '<h2>' . _CLAUDE_ADMIN_USAGE_STATS . '</h2>';
    echo '</div>';
    
    $claude = new ClaudeAPI();
    
    // Time period statistics
    $periods = [
        _CLAUDE_ADMIN_USAGE_TODAY => 1,
        _CLAUDE_ADMIN_USAGE_7_DAYS => 7,
        _CLAUDE_ADMIN_USAGE_30_DAYS => 30,
        _CLAUDE_ADMIN_USAGE_90_DAYS => 90
    ];
    
    echo '<div class="stats-grid">';
    
    foreach ($periods as $label => $days) {
        $stats = $claude->getUsageStats(null, $days);
        echo '<div class="stats-card">';
        echo '<h4>' . $label . '</h4>';
        echo '<div class="stats-data">';
        echo '<div class="stat-item">' . _CLAUDE_ADMIN_USAGE_MESSAGES . ': ' . intval($stats['message_count']) . '</div>';
        echo '<div class="stat-item">' . _CLAUDE_ADMIN_USAGE_USERS . ': ' . intval($stats['unique_users']) . '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Top users
    echo '<div class="top-users-section">';
    echo '<h3>' . _CLAUDE_ADMIN_TOP_USERS_30_DAYS . '</h3>';
    
    $timestamp_limit = time() - (30 * 24 * 60 * 60);
    $sql = "SELECT u.username, COUNT(c.id) as message_count 
            FROM {$prefix}_claude_chat c 
            LEFT JOIN {$prefix}_users u ON c.user_id = u.user_id 
            WHERE c.timestamp > $timestamp_limit
            GROUP BY c.user_id 
            ORDER BY message_count DESC 
            LIMIT 10";
    
    $result = $db->sql_query($sql);
    
    if ($db->sql_numrows($result) > 0) {
        echo '<table class="users-table">';
        echo '<thead><tr><th>' . _CLAUDE_ADMIN_TOP_USERS_USERNAME . '</th><th>' . _CLAUDE_ADMIN_TOP_USERS_MESSAGES . '</th></tr></thead>';
        echo '<tbody>';
        
        while ($row = $db->sql_fetchrow($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
            echo '<td>' . intval($row['message_count']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<p>' . _CLAUDE_ADMIN_TOP_USERS_NO_DATA . '</p>';
    }
    
    echo '</div>';
    CloseTable();
}

function viewClaudeLogs() 
{
    global $admin_file, $prefix, $db;
    OpenTable();    
    echo '<div class="admin-header">';
    echo '<h2>' . _CLAUDE_ADMIN_LOGS . '</h2>';
    echo '<div class="header-actions">';
    echo '<a href="' . $admin_file . '.php?op=clear_logs&csrf_token=' . $_SESSION['csrf_token'] . '" class="btn btn-warning" onclick="return confirm(\'' . _CLAUDE_ADMIN_LOGS_CLEAR_CONFIRM . '\')">' . _CLAUDE_ADMIN_LOGS_CLEAR . '</a>';
    echo '</div>';
    echo '</div>';
    
    $sql = "SELECT * FROM {$prefix}_claude_logs ORDER BY timestamp DESC LIMIT 50";
    $result = $db->sql_query($sql);
    
    if ($db->sql_numrows($result) > 0) {
        echo '<table class="logs-table">';
        echo '<thead>';
        echo '<tr><th>' . _CLAUDE_ADMIN_LOGS_TIME . '</th><th>' . _CLAUDE_ADMIN_LOGS_TYPE . '</th><th>' . _CLAUDE_ADMIN_LOGS_MESSAGE . '</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while ($row = $db->sql_fetchrow($result)) {
            $timestamp = date('Y-m-d H:i:s', intval($row['timestamp']));
            echo '<tr>';
            echo '<td>' . $timestamp . '</td>';
            echo '<td><span class="log-type log-' . htmlspecialchars($row['log_type']) . '">' . strtoupper(htmlspecialchars($row['log_type'])) . '</span></td>';
            echo '<td>' . htmlspecialchars($row['message']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="no-logs">' . _CLAUDE_ADMIN_LOGS_NO_ENTRIES . '</div>';
    }
    
    CloseTable();
}

function clearClaudeLogs() 
{
    global $admin_file, $prefix, $db;
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        die(_CLAUDE_ADMIN_ERROR_CSRF);
    }
    $sql = "DELETE FROM {$prefix}_claude_logs";
    $db->sql_query($sql);
    
    header('Location: ' . $admin_file . '.php?op=claude&msg=logs_cleared');
    exit;
}

function includeAdminAssets() 
{
    global $admin_file;

    // JSON-encode all alert messages to prevent JS syntax errors
    $msg_invalid_api_key       = json_encode(_CLAUDE_ADMIN_INVALID_API_KEY);
    $msg_invalid_max_tokens    = json_encode(_CLAUDE_ADMIN_INVALID_MAX_TOKENS);
    $msg_invalid_temperature   = json_encode(_CLAUDE_ADMIN_INVALID_TEMPERATURE);
    $msg_invalid_rate_limit    = json_encode(_CLAUDE_ADMIN_INVALID_RATE_LIMIT);
    $msg_api_testing           = json_encode(_CLAUDE_ADMIN_API_TESTING);
    $msg_api_test_success      = json_encode(_CLAUDE_ADMIN_API_TEST_SUCCESS);
    $msg_api_test_failed       = json_encode(_CLAUDE_ADMIN_API_TEST_FAILED);
    $msg_api_error             = json_encode(_CLAUDE_ADMIN_ERROR_DATABASE);
    $msg_config_saved          = json_encode(_CLAUDE_ADMIN_CONFIG_SAVED);
    $msg_logs_cleared          = json_encode(_CLAUDE_ADMIN_LOGS_CLEARED);

    echo <<<HTML
<script>
function validateConfig() {
    var apiKey = document.getElementById("api_key").value;
    var maxTokens = Number(document.getElementById("max_tokens").value);
    var temperature = Number(document.getElementById("temperature").value);
    var rate_limit_per_hour = Number(document.getElementById("rate_limit_per_hour").value);

    if (apiKey && !/^sk-ant-[a-zA-Z0-9_-]+$/.test(apiKey)) {
        alert({$msg_invalid_api_key});
        return false;
    }

    if (maxTokens < 100 || maxTokens > 4096) {
        alert({$msg_invalid_max_tokens});
        return false;
    }

    if (temperature < 0 || temperature > 1) {
        alert({$msg_invalid_temperature});
        return false;
    }

    if (rate_limit_per_hour < 30 || rate_limit_per_hour > 100) {
        alert({$msg_invalid_rate_limit});
        return false;
    }

    return true;
}

function testAPI(btn) {
    var originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = {$msg_api_testing};

    fetch("{$admin_file}.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "op=test_api"
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert({$msg_api_test_success});
        } else {
            alert({$msg_api_test_failed});
        }
    })
    .catch(error => {
        alert({$msg_api_error});
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

// Show messages from URL parameters
document.addEventListener("DOMContentLoaded", function() {
    var urlParams = new URLSearchParams(window.location.search);
    var msg = urlParams.get("msg");

    if (msg === "config_saved") alert({$msg_config_saved});
    else if (msg === "logs_cleared") alert({$msg_logs_cleared});
    else if (msg === "invalid_api_key") alert({$msg_invalid_api_key});
});
</script>
HTML;
}
