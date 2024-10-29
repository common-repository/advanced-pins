<?php
namespace apx;
/*
  These functions provide internal utilities and helpers.
  They are for computing, fetching, and parsing data.

  They all return the same value provided the same
  input and have no external side effects.
*/

authorization_check();
function authorization_check() {
  if (!defined('WPINC')) 
    wp_die;
}

// If debugging is enabled, stop exectuion. Else error log it.
function cease($message) {
  if (APX_DEBUG)
    throw new \Exception($message);
  else 
    error_log($message);
}

// Get the visitor's IP based on `$_SERVER` data.
function fetch_ip() {
  foreach(
    [ 'HTTP_CLIENT_IP'
    , 'HTTP_X_FORWARDED_FOR'
    , 'REMOTE_ADDR' ] as $source) {
    if (isset ($_SERVER[$source]))
      return $_SERVER[$source];
  } 

  return;
}

// Get `apx_*` post_meta values for `$post_id`.
function fetch_post_meta($post_id) {
  $post_meta = get_post_meta($post_id);
  foreach($meta_array as $name => $value) {
    if (strpos($name, 'apx') === 0) 
      $post_meta[$name] = $value[0];
    else 
      unset($post_meta[$name]);
  }

  return $post_meta;
}

// Render the template in /templates with passed in `$data`.
function template($file, $data = null) {
  if (is_array($data)) // Pass `$data` to the template through $_POST.
    foreach($data as $key => $value)
      $_POST[$key] = $value;

  $path = dirname(__DIR__)."/templates/$file.php";
  if (file_exists($path))
    require_once $path;

  return;
}

// Gets the applied values of the plugin settings.
function get_plugin_settings() {
  $settings = plugin_settings();

  // Fetch the stored value, or apply the default. 
  foreach($settings as $key => $setting) {
    $name = $setting['name'];
    $value = get_option($name, $setting['default']);
    unset($settings[$key]); // Swap value to be scalar instead of object 
    $settings[$name] = $value;
  }

  return $settings;
}

// Get the path to a plugin asset.
function asset($name) {
  $path = plugins_url('advanced-pins') . "/assets/$name";
    return $path;

  $message = 'Failed to load asset ' . $name;   
  $message .= '\n This is the path generated: " ' . $path; 
  cease($message);

  return '';
}

// Shorcut to deterine if the page request is an Editor.
function is_edit_page() {
  global $pagenow;
  if (!is_admin())
    return false;

  $pages = ['post.php', 'post-new.php', 'page-new.php'];
  if (in_array($pagenow, $pages))
    return true;

  if (isset($_GET['action']) && $_GET['action'] == 'edit')
    return true;

  if (isset($_GET['action2']) && $_GET['action2'] == 'edit')
    return true;

  return false;
}

function is_settings_page() {
  $page = isset($_GET['page'])
    ? $_GET['page'] : '';

  return 'apx_settings' == $page;
}