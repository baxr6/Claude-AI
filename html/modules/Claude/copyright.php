<?php
/**
 * Claude Copyright File Evo Extreme
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


define_once('CP_INCLUDE_DIR', dirname(dirname(dirname(__FILE__))));
require_once CP_INCLUDE_DIR.'/includes/showcp.php';

// To have the Copyright window work in your module just fill the following
// required information and then copy the file "copyright.php" into your
// module's directory. It's all, as easy as it sounds ;)

$author_name = "Deano Welch";
$author_email = "deano.welch@gmail.com";
$author_homepage = "";
$license = "GNU/GPL";
$download_location = "";
$module_version = "1.3.0";
$module_description = "Claude AI module for Evolution Xtreme";

// DO NOT TOUCH THE FOLLOWING COPYRIGHT CODE. YOU'RE JUST ALLOWED TO CHANGE YOUR "OWN"
// MODULE'S DATA (SEE ABOVE) SO THE SYSTEM CAN BE ABLE TO SHOW THE COPYRIGHT NOTICE
// FOR YOUR MODULE/ADDON. PLAY FAIR WITH THE PEOPLE THAT WORKED CODING WHAT YOU USE!!
// YOU ARE NOT ALLOWED TO MODIFY ANYTHING ELSE THAN THE ABOVE REQUIRED INFORMATION.
// AND YOU ARE NOT ALLOWED TO DELETE THIS FILE NOR TO CHANGE ANYTHING FROM THIS FILE IF
// YOU'RE NOT THIS MODULE'S AUTHOR.

show_copyright(
    $author_name, $author_email, $author_homepage, $license, 
    $download_location, $module_version, $module_description
);

?>