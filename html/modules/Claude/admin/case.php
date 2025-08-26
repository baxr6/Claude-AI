<?php
/**
 * Claude Case File Evo Extreme
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

$module_name = basename(dirname(dirname(__FILE__)));
require_once NUKE_MODULES_DIR . $module_name. 
'/admin/language/lang-'.$currentlang.'.php';

switch($op) {

case "save_config":
case "test_api":
case "view_logs":
case "clear_logs":
case "usage_stats":
case "claude":
    include NUKE_MODULES_DIR.$module_name.'/admin/index.php';
    break;

}

?>