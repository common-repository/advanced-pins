<?php
namespace apx;
/***
  Register and create admin hooks, apply admin style, and 
  create our plugin pages.  
*/

add_action('admin_init', '\apx\register_plugin_settings');
add_action('admin_menu', '\apx\handle_admin');
add_action('admin_enqueue_scripts', '\apx\admin_scripts');
add_action('save_post', '\apx\save_meta_fields', 10, 2);
add_action('admin_notices', '\apx\admin_notices');

// The one time plugin introduction. 
add_action('admin_init', '\apx\check_tutorial_status');

function admin_notices() {
  if (is_plugin_active('tasty-pins/tasty-pins.php')) {
    ?>
      <div class="notice notice-error">
        <p>
          <br/>
          Advanced Pins is not compatible with Tasty Pins. 
          <br/>
          <br/>
          If both are active at the same time, you will see Block Editor errors in your posts. 
          <br/>
          <br/>
          <b>Your Pinterest Descriptions will stay</b>, even after deactivating Tasty Pins and activating Advanced Pins!</p>
          <br/>
      </div>
    <?php
    return deactivate_plugins('advanced-pins/advanced-pins.php');
  }

  return null;
}

function check_tutorial_status() {
  // load init.js once from CDN rather than package it with the plugin.
  function load_initjs() {
    add_action('admin_enqueue_scripts', function() {
      wp_enqueue_script('initjs', 'https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/intro.min.js');
    });
  }

  if (is_settings_page() && false === get_option('apx_settings_tutorial_shown')) {
    add_action('apx_settings_page_before', '\apx\show_intro_text');
    update_option('apx_settings_tutorial_shown', true);
  }

  if (is_edit_page() && false === get_option('apx_editor_tutorial_shown')) {

  }
}

function show_intro_text() { 
  ?> 
    <div class="apx-tutorial-intro">
      <h1>Hello, and welcome to Advanced Pins!</h1>
      <p>You are one of <i>the first 10 people</i> to use this plugin, so your constructive criticism and compliments are appreciated.</p>
      <p>This screen shows you the general settings for your Hover Save Button.</p>
      <p>The rest of the plugin lives in the Block Editor. Go ahead and edit a post, and there you will see how we upgraded your Image and Gallery blocks for Pinterest features.</p>
    </div>
  <?php 
}

// Get the applied Post post_meta for Advanced Pins.
function get_metabox_fields($post_id = null) {
  $fields = plugin_metabox_fields();

  if ($post_id === null) {
    foreach($fields as $key => $data) {
      // Transform metabox_fields array to key -> default_value pairs. 
      $name = $data['name'];
      $fields[$name] = $data['default'];
    }

    return $fields;
  }

  /* Search for the target Post with data already provided */
  if (is_numeric($post_id))
    $post = get_post($post_id);
  else // $post_id is already a Post object. 
    $post = $post_id;

  $meta = get_post_meta($post->ID);

  /* Fetch the Post post_meta value or apply the default. */
  foreach($fields as $key => $data) {
    $name = $data['name'];
    unset($fields[$key]);  // Update the array index to be prefixed.
    $value = get_post_meta($post->ID, $name, true);

    if (isset($value)) // Use a previously stored value, iff it exists.
      $fields[$name] = $value;
    else  // use the default.
      $fields[$name] = $data['default'];
  }

  return $fields;
}

// Controller for serving APX assets in admin.
function handle_admin() {
  admin_menu();
  admin_style();

  return null;
}

// Loads APX styles for admin screens.
function admin_style() {
  if (is_settings_page())
    wp_enqueue_style('apx-admin-style', asset('apx-admin-style.css'));

  return null;
}

// Loads script files and their dependencies.
function admin_scripts() {
  global $pagenow;

  $js_path = plugins_url('advanced-pins/dist/main.js');
  $dependencies = 
    [ 'jquery'
    , 'wp-components'
    , 'wp-element'
    , 'wp-compose'
    , 'wp-edit-post' ];

  wp_enqueue_media();
  wp_enqueue_script('apx-admin', $js_path, $dependencies, false, false);
 
  if (is_edit_page()) {
    $image_block = plugins_url('advanced-pins/dist/image.js');
    $gallery_block = plugins_url('advanced-pins/dist/pin-gallery.js');

    wp_enqueue_script('apx-image-component', $image_block, $dependencies, false, true);
    wp_enqueue_script('apx-gallery-component', $gallery_block, $dependencies, false, true);
  }

  return null;
}

// Creates an Advanced Pins Main Menu.
function admin_menu() {
  $menu_key = add_menu_page(
    __('Advanced Pins'),
    __('Advanced Pins'),
    'administrator',
    'apx_settings',
    '\apx\display_plugin_settings',
    'dashicons-palmtree',
    20
  );

  return null;
}

// Wrapper to sanitize meta fields before storage.
function update_post_meta($id, $key, $value) {
  /* $key is already prefixed. Sanitize for safety. */
  $k = sanitize_key($key);

  if (strpos($key, 'description') != -1)
    $v = sanitize_textarea_field($value);
  else 
    $v = sanitize_text_field($value);

  return \update_post_meta($id, $k, $v);
}


// @hook `save_post` @action apx_save_meta_fields
// Controller for ajax callback when a Post is updated.
function save_meta_fields($post_id, $post) {
  if (!current_user_can('administrator'))
    return cease('You do not have permission to edit these fields.');

  if (empty($_POST['action']))
    return; 

  do_action('apx_save_meta_fields', $post_id);

  foreach(plugin_metabox_fields() as $key => $data) {
    $name = $data['name'];
    $value = isset($_POST[$name])
      ? sanitize_text_field($_POST[$name]) : $data['default'];

    $updated = update_post_meta($post_id, $name, $value);
    update_related_fields($key, $value, $post);
  }

  return null;
}

// Hook for updating field dependencies.
function update_related_fields($key, $value, $post) {
  do_action('apx_update_field', $key, $value, $post);

  if ($key == 'image_source' && $value == 'gallery')
    delete_post_meta($post, 'apx_pinterest_image_id');

  return null;
}

// Get stored data, fill in the metabox, and render.
function render_metabox() {
  global $post;
  $fields = get_metabox_fields($post);
  template('metabox', $fields);
  return null;
}

// Tells WP about our settings to enable autosaving.
function register_plugin_settings() {
  $core_settings = plugin_settings();
  $all_settings = apply_filters('apx_settings', $core_settings);

  if (!is_array($all_settings) || empty($all_settings)) {
    error_log("Attempted to receive third party settings, but could not accept this data on filter `apx_settings`: " . var_export($all_settings, 1));
    $all_settings = $core_settings;
  }

  foreach($all_settings as $key => $settings_meta) {
    $name = $settings_meta['name'];
    register_setting('apx', $name, $settings_meta);
  }

  /* Register our metabox in the Post Editor. */
  add_action('add_meta_boxes', function() {
    add_meta_box('apx_metabox', __('Advanced Pins'), '\apx\render_metabox', null, 'side', 'high');
  });
  return null;
}

// Callback for `admin_menu()` to display plugin settings on the Advanced Pins page.
function display_plugin_settings() {
  $callback = function() {return;/* Required as a param to add_settings_section*/};
  add_settings_section('apx_general', 'Settings', $callback, 'apx');

  $applied_settings = plugin_settings();
  foreach($applied_settings as $key => $settings_meta) {
    $name = $settings_meta['name'];
    $settings_meta['value'] = get_option($name, $settings_meta['default']);

    switch ($settings_meta['type']) {
      case 'number' : // Let type number be the same as a numeric string.
      case 'string' : 
        $display_cb = '\apx\display_input_text';
        break;
      case 'boolean' : 
        $display_cb = '\apx\display_input_toggle';
        break;
      case 'select' :  
        $display_cb = '\apx\display_input_select';
        break;
    }

    // Register the setting with WP for automatic saving and upating.
    add_settings_field 
      ( $name
      , $settings_meta['display_name']
      , function() use ($key, $settings_meta, $display_cb) { 
          return $display_cb($key, $settings_meta); }
      , 'apx'
      , 'apx_general'
      , ['label_for' => $settings_meta['name']] );

    $applied_settings[$key] = $settings_meta['value'];
  }

  template('settings-page', $applied_settings);
  return null;
}

// Render an <input type="text" to the APX settings page.
function display_input_text($key, $field_data) {
  $placeholder = isset($field_data['placeholder'])
    ? $field_data['placeholder'] : '';

  $description = isset($field_data['description'])
    ? $field_data['description'] : '';

echo <<<INPUT
<input 
  type="text" 
  name="apx_{$key}" 
  value="{$field_data['value']}"
  placeholder="{$placeholder}" />
<p>{$description}</p>
INPUT;
  return null;
}

// Render an <input type="select" to the APX settings page.
function display_input_select($key, $field_data) {
  echo "<select name='apx_{$key}' autocomplete='off' >";
  foreach($field_data['options'] as $key => $name) {
    $selected = $key == $field_data['value'] 
      ? 'selected="selected"' : '';
    echo "<option value='$key' {$selected}>$name</option>";
  }
  echo "</select>";
  return null;
}

// Render an <input type="checkbox" to the APX settings page.
function display_input_toggle($key, $field_data) {
  $checked = $field_data['value'] === 'on' 
    ? 'checked="checked"' : '';
echo <<<CHECKBOX
<input id="apx_{$key}" type="checkbox" name="apx_{$key}" {$checked} />
CHECKBOX;
  return null;
}