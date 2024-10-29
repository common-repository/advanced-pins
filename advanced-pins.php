<?php
/**
 * Plugin Name: Advanced Pins
 *
 * @package     Advanced Pins
 * @author      Pin Wave Media
 * @copyright   2019 
 * @license     GPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name: Advanced Pins
 * Plugin URI:  https://advancedpins.com
 * Description: Turn your posts into Pinterest Winners. 
 * Version:     0.6.2
 * Author:      Pin Wave Media
 * Text Domain: apx
 * License:     GPL-3.0+ 
 */

define('APX_VERSION', '0.6.2');

$debug = false || defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY;
define('APX_DEBUG', $debug);

foreach(
  [ 'data'
  , 'functions'
  , 'frontend'
  , 'admin' 
  , 'pageload' ]
  as $file) 
  require_once __DIR__."/php/$file.php";

register_activation_hook(__FILE__, 'activate_advanced_pins');

function activate_advanced_pins() {
    update_option('apx_settings_tutorial_shown', false);
}
