<?php
/**
 * Claude Admin Language File Evo Extreme
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

if (!defined('ADMIN_FILE')) {
    die("You can't access this file directly...");
}

// =============================================================================
// ADMIN INTERFACE MAIN
// =============================================================================
define_once('_CLAUDE_ADMIN_TITLE', 'Claude AI Chat Administration');
define_once('_CLAUDE_ADMIN_SUBTITLE', 'Configure Claude AI Chat');
define_once('_CLAUDE_ADMIN_DASHBOARD', 'Claude AI Dashboard');

// =============================================================================
// NAVIGATION & TABS
// =============================================================================
define_once('_CLAUDE_ADMIN_TAB_CONFIG', 'Configuration');
define_once('_CLAUDE_ADMIN_TAB_STATS', 'Usage Statistics');
define_once('_CLAUDE_ADMIN_TAB_LOGS', 'System Logs');
define_once('_CLAUDE_ADMIN_TAB_USERS', 'User Management');
define_once('_CLAUDE_ADMIN_BACK_TO_CONFIG', '← Back to Configuration');
define_once('_CLAUDE_ADMIN_BACK_TO_MAIN', '← Back to Main Admin');

// =============================================================================
// API CONFIGURATION
// =============================================================================
define_once('_CLAUDE_ADMIN_API_CONFIG', 'API Configuration');
define_once(
    '_CLAUDE_ADMIN_API_CONFIG_DESC', 'Configure your Anthropic Claude API settings'
);

// API Key Settings
define_once('_CLAUDE_ADMIN_API_KEY', 'Claude API Key');
define_once(
    '_CLAUDE_ADMIN_API_KEY_DESC', 'Your Anthropic Claude API key (sk-ant-)'
);
define_once('_CLAUDE_ADMIN_API_KEY_PLACEHOLDER', 'Enter your Claude API key');
define_once('_CLAUDE_ADMIN_API_KEY_CURRENT', 'Current Key');
define_once('_CLAUDE_ADMIN_API_KEY_NOT_SET', 'Not configured');
define_once('_CLAUDE_ADMIN_API_KEY_MASKED', 'Key is set (masked for security)');
define_once('_CLAUDE_ADMIN_GETAPIKEY', 'Get your API key from');
define_once('_CLAUDE_ADMIN_ANTHROPIC_CONSOLE', 'Anthropic Console');

// Model Selection
define_once('_CLAUDE_ADMIN_MODEL', 'Claude Model');
define_once(
    '_CLAUDE_ADMIN_MODEL_DESC', 'Choose the Claude model based on your needs'
);
define_once(
    '_CLAUDE_ADMIN_MODEL_HAIKU', 
    'Claude 3 Haiku (Fast & Economical - $0.25/$1.25 per 1M tokens)'
);
define_once(
    '_CLAUDE_ADMIN_MODEL_SONNET', 
    'Claude 3 Sonnet (Balanced - $3/$15 per 1M tokens)'
);
define_once(
    '_CLAUDE_ADMIN_MODEL_OPUS', 
    'Claude 3 Opus (Most Capable - $15/$75 per 1M tokens)'
);

// API Parameters
define_once('_CLAUDE_ADMIN_MAX_TOKENS', 'Maximum Tokens');
define_once(
    '_CLAUDE_ADMIN_MAX_TOKENS_DESC', 
    'Maximum tokens in Claude\'s response (100-4096). ' .
    'Higher values = longer responses but higher costs.'
);
define_once('_CLAUDE_ADMIN_TEMPERATURE', 'Temperature');
define_once(
    '_CLAUDE_ADMIN_TEMPERATURE_DESC', 
    'Controls creativity level (0.0 = focused and deterministic, ' .
    '1.0 = creative and varied). Recommended: 0.7'
);
define_once('_CLAUDE_ADMIN_RATE_LIMIT', 'Rate Limit (per hour)');
define_once(
    '_CLAUDE_ADMIN_RATE_LIMIT_DESC', 
    'Maximum messages per user per hour. ' .
    'Recommended: 30-100 depending on your usage needs.'
);
define_once('_CLAUDE_ADMIN_CONTEXT_LIMIT', 'Conversation Context Limit');
define_once(
    '_CLAUDE_ADMIN_CONTEXT_LIMIT_DESC', 
    'Number of previous messages to include for context. ' .
    'Higher = better context but higher costs.'
);

// =============================================================================
// CONFIGURATION ACTIONS
// =============================================================================
define_once('_CLAUDE_ADMIN_SAVE_CONFIG', 'Save Configuration');
define_once('_CLAUDE_ADMIN_TEST_API', 'Test API Connection');
define_once('_CLAUDE_ADMIN_RESET_CONFIG', 'Reset to Defaults');
define_once('_CLAUDE_ADMIN_CONFIG_SAVED', 'Configuration saved successfully!');
define_once(
    '_CLAUDE_ADMIN_CONFIG_ERROR', 'Error saving configuration. Please try again.'
);
define_once('_CLAUDE_ADMIN_API_TEST_SUCCESS', '✓ API Connection Successful!');
define_once(
    '_CLAUDE_ADMIN_API_TEST_FAILED', '✗ API Connection Failed. ' .
    'Please check your API key and configuration.'
);
define_once('_CLAUDE_ADMIN_API_TESTING', 'Testing API connection...');

// Validation Messages
define_once(
    '_CLAUDE_ADMIN_INVALID_API_KEY', 'Invalid API key format. ' .
    'API key should start with "sk-ant-"'
);
define_once(
    '_CLAUDE_ADMIN_INVALID_MAX_TOKENS', 
    'Max tokens must be between 100 and 4096'
);
define_once(
    '_CLAUDE_ADMIN_INVALID_TEMPERATURE', 
    'Temperature must be between 0.0 and 1.0'
);
define_once(
    '_CLAUDE_ADMIN_INVALID_RATE_LIMIT', 
    'Rate limit must be between 10 and 1000 messages per hour'
);
define_once(
    '_CLAUDE_ADMIN_VALIDATION_ERROR', 'Please correct the errors above and try again'
);

// =============================================================================
// MODULE STATUS & MONITORING
// =============================================================================
define_once('_CLAUDE_ADMIN_MODULE_STATUS', 'Module Status');
define_once('_CLAUDE_ADMIN_SYSTEM_HEALTH', 'System Health Check');
define_once('_CLAUDE_ADMIN_API_STATUS', 'API Status');
define_once('_CLAUDE_ADMIN_DATABASE_STATUS', 'Database Status');
define_once('_CLAUDE_ADMIN_PERMISSIONS_STATUS', 'File Permissions');
define_once('_CLAUDE_ADMIN_MODULE_VERSION', 'Module Version');

// Status Indicators
define_once('_CLAUDE_ADMIN_STATUS_CONNECTED', '✓ Connected');
define_once('_CLAUDE_ADMIN_STATUS_DISCONNECTED', '✗ Connection Failed');
define_once('_CLAUDE_ADMIN_STATUS_TABLES_OK', '✓ Tables OK');
define_once('_CLAUDE_ADMIN_STATUS_TABLES_MISSING', '✗ Tables Missing');
define_once('_CLAUDE_ADMIN_STATUS_PERMISSIONS_OK', '✓ Permissions OK');
define_once('_CLAUDE_ADMIN_STATUS_PERMISSIONS_ERROR', '✗ Permission Issues');

// Quick Stats
define_once('_CLAUDE_ADMIN_MESSAGES_TODAY', 'Messages Today');
define_once('_CLAUDE_ADMIN_MESSAGES_7_DAYS', 'Messages (Last 7 Days)');
define_once('_CLAUDE_ADMIN_MESSAGES_30_DAYS', 'Messages (Last 30 Days)');
define_once('_CLAUDE_ADMIN_ACTIVE_USERS_TODAY', 'Active Users Today');
define_once('_CLAUDE_ADMIN_ACTIVE_USERS_7_DAYS', 'Active Users (Last 7 Days)');
define_once('_CLAUDE_ADMIN_ACTIVE_USERS_30_DAYS', 'Active Users (Last 30 Days)');

// =============================================================================
// USAGE STATISTICS
// =============================================================================
define_once('_CLAUDE_ADMIN_USAGE_STATS', 'Usage Statistics');
define_once('_CLAUDE_ADMIN_USAGE_OVERVIEW', 'Usage Overview');
define_once('_CLAUDE_ADMIN_USAGE_TODAY', 'Today');
define_once('_CLAUDE_ADMIN_USAGE_7_DAYS', 'Last 7 Days');
define_once('_CLAUDE_ADMIN_USAGE_30_DAYS', 'Last 30 Days');
define_once('_CLAUDE_ADMIN_USAGE_90_DAYS', 'Last 90 Days');
define_once('_CLAUDE_ADMIN_USAGE_ALL_TIME', 'All Time');
define_once('_CLAUDE_ADMIN_USAGE_MESSAGES', 'Total Messages');
define_once('_CLAUDE_ADMIN_USAGE_USERS', 'Active Users');
define_once('_CLAUDE_ADMIN_USAGE_AVG_PER_USER', 'Average Messages per User');
define_once(
    '_CLAUDE_ADMIN_USAGE_NO_DATA', 'No usage data available for this period.'
);

// Top Users Section
define_once('_CLAUDE_ADMIN_TOP_USERS', 'Most Active Users');
define_once('_CLAUDE_ADMIN_TOP_USERS_30_DAYS', 'Most Active Users (Last 30 Days)');
define_once('_CLAUDE_ADMIN_TOP_USERS_USERNAME', 'Username');
define_once('_CLAUDE_ADMIN_TOP_USERS_MESSAGES', 'Messages');
define_once('_CLAUDE_ADMIN_TOP_USERS_LAST_ACTIVE', 'Last Active');
define_once('_CLAUDE_ADMIN_TOP_USERS_NO_DATA', 'No user activity data available.');

// API Usage & Costs
define_once('_CLAUDE_ADMIN_API_USAGE', 'API Usage');
define_once('_CLAUDE_ADMIN_TOKENS_USED', 'Tokens Used');
define_once('_CLAUDE_ADMIN_ESTIMATED_COST', 'Estimated Cost');
define_once('_CLAUDE_ADMIN_REQUESTS_MADE', 'API Requests');
define_once('_CLAUDE_ADMIN_AVERAGE_RESPONSE_TIME', 'Avg Response Time');

// =============================================================================
// SYSTEM LOGS
// =============================================================================
define_once('_CLAUDE_ADMIN_LOGS', 'System Logs');
define_once('_CLAUDE_ADMIN_LOGS_SUBTITLE', 'Monitor errors and system events');
define_once('_CLAUDE_ADMIN_LOGS_OVERVIEW', 'Log Overview');
define_once('_CLAUDE_ADMIN_LOGS_TIME', 'Time');
define_once('_CLAUDE_ADMIN_LOGS_TYPE', 'Type');
define_once('_CLAUDE_ADMIN_LOGS_MESSAGE', 'Message');
define_once('_CLAUDE_ADMIN_LOGS_USER', 'User');
define_once('_CLAUDE_ADMIN_LOGS_IP', 'IP Address');
define_once('_CLAUDE_ADMIN_LOGS_CLEAR', 'Clear All Logs');
define_once('_CLAUDE_ADMIN_LOGS_EXPORT', 'Export Logs');
define_once('_CLAUDE_ADMIN_LOGS_FILTER', 'Filter Logs');
define_once(
    '_CLAUDE_ADMIN_LOGS_CLEAR_CONFIRM', 
    'Are you sure you want to clear all system logs? This cannot be undone.'
);
define_once(
    '_CLAUDE_ADMIN_LOGS_CLEARED', 'System logs have been cleared successfully.'
);
define_once('_CLAUDE_ADMIN_LOGS_NO_ENTRIES', 'No log entries found.');

// Log Types & Levels
define_once('_CLAUDE_ADMIN_LOG_ERROR', 'ERROR');
define_once('_CLAUDE_ADMIN_LOG_WARNING', 'WARNING');
define_once('_CLAUDE_ADMIN_LOG_INFO', 'INFO');
define_once('_CLAUDE_ADMIN_LOG_DEBUG', 'DEBUG');
define_once('_CLAUDE_ADMIN_LOG_API_ERROR', 'API ERROR');
define_once('_CLAUDE_ADMIN_LOG_SECURITY', 'SECURITY');

// Log Filters
define_once('_CLAUDE_ADMIN_LOGS_SHOW_ALL', 'Show All');
define_once('_CLAUDE_ADMIN_LOGS_SHOW_ERRORS', 'Errors Only');
define_once('_CLAUDE_ADMIN_LOGS_SHOW_WARNINGS', 'Warnings Only');
define_once('_CLAUDE_ADMIN_LOGS_LAST_24H', 'Last 24 Hours');
define_once('_CLAUDE_ADMIN_LOGS_LAST_WEEK', 'Last Week');

// =============================================================================
// USER MANAGEMENT
// =============================================================================
define_once('_CLAUDE_ADMIN_USER_MANAGEMENT', 'User Management');
define_once('_CLAUDE_ADMIN_USER_PERMISSIONS', 'User Permissions');
define_once('_CLAUDE_ADMIN_USER_RATE_LIMITS', 'Rate Limit Overrides');
define_once('_CLAUDE_ADMIN_BLOCKED_USERS', 'Blocked Users');
define_once('_CLAUDE_ADMIN_USER_SEARCH', 'Search Users');
define_once('_CLAUDE_ADMIN_USER_BLOCK', 'Block User');
define_once('_CLAUDE_ADMIN_USER_UNBLOCK', 'Unblock User');
define_once('_CLAUDE_ADMIN_USER_RESET_LIMIT', 'Reset Rate Limit');
define_once('_CLAUDE_ADMIN_USER_VIEW_HISTORY', 'View Chat History');

// =============================================================================
// SECURITY & ADMIN
// =============================================================================
define_once('_CLAUDE_ADMIN_SECURITY', 'Security Settings');
define_once('_CLAUDE_ADMIN_SECURITY_CSRF', 'CSRF Protection');
define_once('_CLAUDE_ADMIN_SECURITY_RATE_LIMITING', 'Rate Limiting');
define_once('_CLAUDE_ADMIN_SECURITY_CONTENT_FILTER', 'Content Filtering');
define_once('_CLAUDE_ADMIN_SECURITY_LOGS', 'Security Logging');
define_once('_CLAUDE_ADMIN_SECURITY_ACCESS_CONTROL', 'Access Control');

// Error Messages (Admin)
define_once(
    '_CLAUDE_ADMIN_ERROR_NO_PERMISSION', 
    'You do not have permission to access this area.'
);
define_once(
    '_CLAUDE_ADMIN_ERROR_CSRF', 'Security token mismatch. Please try again.'
);
define_once(
    '_CLAUDE_ADMIN_ERROR_DATABASE', 
    'Database error occurred. Please check the system logs.'
);
define_once(
    '_CLAUDE_ADMIN_ERROR_FILE_PERMISSIONS', 
    'File permission error. Please check directory permissions.'
);

// =============================================================================
// INSTALLATION & MAINTENANCE
// =============================================================================

define_once('_CLAUDE_ADMIN_CLEAR_CACHE', 'Clear Cache');

// =============================================================================
// HELP & DOCUMENTATION
// =============================================================================
define_once('_CLAUDE_ADMIN_HELP', 'Help & Documentation');
define_once('_CLAUDE_ADMIN_HELP_API_SETUP', 'API Setup Guide');
define_once('_CLAUDE_ADMIN_HELP_TROUBLESHOOTING', 'Troubleshooting');
define_once('_CLAUDE_ADMIN_HELP_FAQ', 'Frequently Asked Questions');
define_once(
    '_CLAUDE_ADMIN_HELP_API_KEY', 
    'You can get your free API key by creating an account at console.anthropic.com'
);
define_once(
    '_CLAUDE_ADMIN_HELP_MODEL_SELECTION', 
    'Haiku is fastest and cheapest, Sonnet is balanced, ' .
    'Opus is most capable but expensive'
);
define_once(
    '_CLAUDE_ADMIN_HELP_RATE_LIMITING', 
    'Rate limiting prevents abuse and helps control API costs'
);
define_once(
    '_CLAUDE_ADMIN_HELP_TEMPERATURE', 
    'Lower temperature (0.1-0.3) for factual responses, ' .
    'higher (0.7-0.9) for creative content'
);

// =============================================================================
// BUTTONS & ACTIONS
// =============================================================================
define_once('_CLAUDE_ADMIN_SAVE', 'Save');
define_once('_CLAUDE_ADMIN_CANCEL', 'Cancel');
define_once('_CLAUDE_ADMIN_RESET', 'Reset');
define_once('_CLAUDE_ADMIN_DELETE', 'Delete');
define_once('_CLAUDE_ADMIN_EDIT', 'Edit');
define_once('_CLAUDE_ADMIN_VIEW', 'View');
define_once('_CLAUDE_ADMIN_EXPORT', 'Export');
define_once('_CLAUDE_ADMIN_IMPORT', 'Import');
define_once('_CLAUDE_ADMIN_REFRESH', 'Refresh');
define_once('_CLAUDE_ADMIN_UPDATE', 'Update');
define_once('_CLAUDE_ADMIN_APPLY', 'Apply');
define_once('_CLAUDE_ADMIN_CLOSE', 'Close');

?>