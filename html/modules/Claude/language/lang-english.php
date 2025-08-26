<?php

/**
 * Claude Module Language File Index Evo Extreme
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

// =============================================================================
// FRONTEND CHAT INTERFACE
// =============================================================================
define_once('_CLAUDE_TITLE', 'Claude AI Chat');
define_once(
    '_CLAUDE_WELCOME',
    'Hello %s! I\'m Claude, an AI assistant. How can I help you today?'
);
define_once('_CLAUDE_INPUT_PLACEHOLDER', 'Type your message here...');
define_once('_CLAUDE_SEND_BUTTON', 'Send');
define_once('_CLAUDE_CLEAR', 'Clear Chat');
define_once(
    '_CLAUDE_CLEAR_CONFIRM',
    'Are you sure you want to clear your chat history? This cannot be undone.'
);
define_once(
    '_CLAUDE_HISTORY_CLEARED',
    'Chat history has been cleared. How can I help you today?'
);

// =============================================================================
// USER INTERFACE ELEMENTS
// =============================================================================
define_once('_CLAUDE_LOADING', 'Loading...');
define_once('_CLAUDE_SENDING', 'Sending...');
define_once('_CLAUDE_CHARACTERS_REMAINING', 'characters remaining');
define_once('_CLAUDE_CHARACTERS_LIMIT_EXCEEDED', 'Character limit exceeded');
define_once('_CLAUDE_CONFIRM', 'Confirm');
define_once('_CLAUDE_CANCEL', 'Cancel');
define_once('_CLAUDE_CLOSE', 'Close');
define_once('_CLAUDE_REFRESH', 'Refresh');

// =============================================================================
// ERROR MESSAGES (USER-FACING)
// =============================================================================
define_once(
    '_CLAUDE_ERROR_UNAUTHORIZED',
    'You must be logged in to use Claude AI Chat.'
);
define_once(
    '_CLAUDE_ERROR_RATE_LIMIT',
    'You have reached the message limit. Please wait before sending another message.'
);
define_once(
    '_CLAUDE_ERROR_INVALID_MESSAGE',
    'Invalid message content or length exceeded.'
);
define_once('_CLAUDE_ERROR_EMPTY_MESSAGE', 'Message cannot be empty.');
define_once(
    '_CLAUDE_ERROR_SAVE_FAILED',
    'Failed to save your message. Please try again.'
);
define_once(
    '_CLAUDE_ERROR_API_UNAVAILABLE',
    'AI service is temporarily unavailable. Please try again later.'
);
define_once(
    '_CLAUDE_ERROR_API_ERROR',
    'AI service error. Please try again later.'
);
define_once(
    '_CLAUDE_ERROR_NETWORK',
    'Network error occurred. Please check your connection.'
);
define_once(
    '_CLAUDE_ERROR_SESSION_EXPIRED',
    'Your session has expired. Please refresh the page.'
);

// =============================================================================
// API RESPONSE MESSAGES
// =============================================================================
define_once(
    '_CLAUDE_API_RESPONSE_ERROR',
    'I apologize, but I encountered an error processing your request. Please try again.'
);
define_once(
    '_CLAUDE_API_RESPONSE_TIMEOUT',
    'The request timed out. Please try again with a shorter message.'
);
define_once(
    '_CLAUDE_API_RESPONSE_RATE_LIMITED',
    'Too many requests. Please wait a moment before trying again.'
);
define_once(
    '_CLAUDE_API_RESPONSE_GENERIC_ERROR',
    'Something went wrong. Please try again in a moment.'
);

// =============================================================================
// SECURITY MESSAGES (USER-FACING)
// =============================================================================
define_once(
    '_CLAUDE_SECURITY_CSRF_ERROR',
    'Security verification failed. Please refresh the page and try again.'
);
define_once(
    '_CLAUDE_SECURITY_INVALID_REQUEST',
    'Invalid request. Please try again.'
);
define_once(
    '_CLAUDE_SECURITY_ACCESS_DENIED',
    'Access denied. Please log in to continue.'
);

// =============================================================================
// CHAT FUNCTIONALITY
// =============================================================================
define_once('_CLAUDE_CHAT_WELCOME_TITLE', 'Welcome to Claude AI');
define_once('_CLAUDE_CHAT_SUBTITLE', 'Your intelligent AI assistant');
define_once('_CLAUDE_CHAT_PLACEHOLDER_THINKING', 'Claude is thinking...');
define_once('_CLAUDE_CHAT_PLACEHOLDER_TYPING', 'Claude is typing...');
define_once('_CLAUDE_CHAT_MESSAGE_SENT', 'Message sent');
define_once('_CLAUDE_CHAT_MESSAGE_RECEIVED', 'Response received');
define_once(
    '_CLAUDE_CHAT_HISTORY_EMPTY',
    'No chat history yet. Start a conversation!'
);

// =============================================================================
// HELP & GUIDANCE (USER)
// =============================================================================
define_once('_CLAUDE_HELP_GETTING_STARTED', 'Getting Started');
define_once(
    '_CLAUDE_HELP_GETTING_STARTED_TEXT',
    'Simply type your question or message in the box below and press Send. ' .
    'Claude can help with various tasks including answering questions, writing, analysis, ' .
    'and creative projects.'
);
define_once('_CLAUDE_HELP_TIPS_TITLE', 'Tips for Better Conversations');
define_once('_CLAUDE_HELP_TIP_1', 'Be specific and clear in your questions');
define_once('_CLAUDE_HELP_TIP_2', 'Feel free to ask follow-up questions');
define_once(
    '_CLAUDE_HELP_TIP_3',
    'Claude can remember context within your conversation'
);
define_once('_CLAUDE_HELP_PRIVACY', 'Your conversations are private and secure');

// =============================================================================
// ACCESSIBILITY
// =============================================================================
define_once('_CLAUDE_ACCESSIBILITY_CHAT_REGION', 'Chat conversation area');
define_once('_CLAUDE_ACCESSIBILITY_INPUT_LABEL', 'Type your message to Claude');
define_once('_CLAUDE_ACCESSIBILITY_SEND_BUTTON', 'Send message to Claude');
define_once('_CLAUDE_ACCESSIBILITY_CLEAR_BUTTON', 'Clear chat history');
define_once('_CLAUDE_ACCESSIBILITY_MESSAGE_FROM_USER', 'Message from you');
define_once(
    '_CLAUDE_ACCESSIBILITY_MESSAGE_FROM_CLAUDE',
    'Response from Claude'
);
define_once('_CLAUDE_ACCESSIBILITY_TIMESTAMP', 'Message timestamp');

// =============================================================================
// MOBILE/RESPONSIVE
// =============================================================================
define_once('_CLAUDE_MOBILE_MENU', 'Chat Options');
define_once('_CLAUDE_MOBILE_MINIMIZE', 'Minimize Chat');
define_once('_CLAUDE_MOBILE_EXPAND', 'Expand Chat');
define_once('_CLAUDE_MOBILE_SCROLL_TO_BOTTOM', 'Scroll to latest message');

// =============================================================================
// CONNECTION STATUS
// =============================================================================
define_once('_CLAUDE_CONNECTION_ONLINE', 'Connected');
define_once('_CLAUDE_CONNECTION_OFFLINE', 'Disconnected');
define_once('_CLAUDE_CONNECTION_RECONNECTING', 'Reconnecting...');
define_once('_CLAUDE_CONNECTION_FAILED', 'Connection failed');