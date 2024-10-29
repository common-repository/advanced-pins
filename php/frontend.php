<?php
namespace apx;
/*
Controls front end/vistior side of website.

These functions may have output to the page as an echo or a return value of HTML.
*/
authorization_check();

add_action('parse_request', '\apx\parse_request', 1); // High priority.
add_action('wp_enqueue_scripts', '\apx\frontend_scripts');

// Record image performance by reading apx referrals. 
function parse_request($query) {
  $server = $_SERVER; 
  $referrer = isset($server['HTTP_REFERER'])
    ? $server['HTTP_REFERER'] : '';

  if (false === strpos($referrer, 'pinterest'))
    return $query;

  $request = $_REQUEST;
  if (empty($request['apx_ref']))
    return;

  // Defer until `wp` action so global `$post` is defined.
  add_action('wp', function() use ($request) {
    global $post;
    $ref_id = sanitize_text_field($request['apx_ref']);
    increment_pin_records($ref_id, $post); 
  });
}

/**
 * Each image may be pinned from a different post. 
 * The `apx_ref` query var records which $post_id the Pin came from. */
function increment_pin_records($pin_image, $post_id) {
  $pin_count = get_post_meta($pin_image, 'apx_pin_count', true);
  $post_count = get_post_meta($pin_image, 'apx_traffic_post_id_' . $post_id->ID, true);

  if (!$pin_count) { // Start a new record for this $post_id
    update_post_meta($pin_image, 'apx_pin_count', 1);
    update_post_meta($pin_image, 'apx_traffic_post_id_' . $post_id->ID, 1);
  }
  else { // Increment the record by one. 
    update_post_meta($pin_image, 'apx_pin_count', $pin_count + 1);
    update_post_meta($pin_image, 'apx_traffic_post_id_' . $post_id->ID, (int) $post_count + 1);
  }
}

function frontend_scripts() {
  $pin_sdk = 'https://assets.pinterest.com/sdk/sdk.js';
  $js_path = plugins_url('/advanced-pins/dist/main.js');

  $dependencies = [ 'apx-pin-sdk', 'jquery' ];

  wp_enqueue_script('apx-pin-sdk', $pin_sdk, [], false, true);
  wp_enqueue_script('apx-frontend', $js_path, $dependencies, false, false);
}