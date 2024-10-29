<?php
namespace apx;
/* 
  Runs processes necessary for all requests, front or back end.
*/
add_action('init', '\apx\register_post_metas');
add_action('wp_enqueue_scripts', '\apx\localize_variables', 1000);
add_action('admin_enqueue_scripts', '\apx\localize_variables', 1000);
add_action('wp_enqueue_scripts', '\apx\load_common_scripts');
add_action('admin_enqueue_scripts', '\apx\load_common_scripts');

function load_common_scripts() {
  wp_enqueue_style('apx-style', asset('apx-style.css'));
}

// Applies post's Pinterest settings to the `apx.pin` object.
function apply_force_pin_data($post, $apx) {
  if (!is_object($post))
    return $apx;

  $apx['forcePin'] = [];
  $image_id = get_post_meta($post->ID, 'apx_pinterest_image_id', true);
  if ($image_id) {
    $image_data = wp_get_attachment_image_src($image_id, 'full');
    $apx['forcePin']['media'] = $image_data[0];
    $apx['forcePin']['url'] = urlencode(get_permalink($post));
  }

  $description = get_post_meta($post->ID, 'apx_description', true);
  if (!empty($description))
    $apx['forcePin']['description'] = $description;

  if (empty($apx['forcePin']))
    unset($apx['forcePin']);

  return $apx;
}

// Register these meta fields to describe the shape for WP objects.
function register_post_metas() {
  $fields = plugin_metabox_fields();

  foreach($fields as $key => $meta) {
    $name = $meta['name'];
    register_post_meta(''/* all post types */, $name, $meta);
  } 
}