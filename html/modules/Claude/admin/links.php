<?php
/**
 * Claude Link File Evo Extreme
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
    die('Access Denied');
}

global $admin_file;
adminmenu($admin_file.'.php?op=claude', 'Claude', 'Claude_AI.png');

?>