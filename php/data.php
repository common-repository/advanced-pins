<?php
namespace apx;

// A list of all raw plugin metabox fields as key -> metadata.
function plugin_metabox_fields() {
  $fields =  
    [ 'image_source' =>
      [ 'default' => 'gallery'
      , 'type' => 'string'
      , 'sanitize_callback' => 'sanitize_text_field' ]

    , 'description' => 
      [ 'default' => ''
      , 'type' => 'string'
      , 'sanitize_callback' => 'sanitize_textarea_field' ]

    , 'pinterest_image_id' => 
      [ 'default' => ''
      , 'type' => 'integer'
      , 'sanitize_callback' => 'sanitize_text_field' ] ];

  /** 
   * Apply common fields to all meta of $fields. 
   * 
   * !! Important !! 
   * $meta['package'] will prefix the $key in the database. 
   * This keeps data keys semantic during development. */
  foreach($fields as $key => $data) {
    $data['key'] = $key;
    $data['name'] = 'apx_'.$key;
    $data['package'] = 'apx';
    $data['show_in_rest'] = false;
    $data['single'] = true;
    $fields[$key] = $data;
    register_post_meta(''/*all post types*/, $key, $data);
  }

  return $fields;
}

// A list of the core plugin settings as name -> setting data.
function plugin_settings() {
  $settings = 
    [ 'hover_button_style' =>
      [ 'display_name'     => __('Hover Style')
      , 'description' => __('Select the style to apply to your Hover Save buttons')
      , 'type'     => 'select'
      , 'default'  => 'classic'
      , 'options'  => 
        [ 'classic' => __('Classic')
        , 'bold'    => __('Bold')
        , 'round'   => __('Round') ] ]
        
    , 'hover_button_text' => 
        [ 'display_name'    => __('Hover Button Text')
        , 'description' => 'What does your hover button say?'
        , 'type'    => 'string'
        , 'default' => 'Save' ]

    , 'hover_position_horizontal' => 
      [ 'display_name'    => __('Horizontal placement')
      , 'description' => 'Where to place the button horizontally in the image.'
      , 'type'    => 'select'
      , 'default' => 'center'
      , 'options' =>
        [ 'left'    => __('Left')
        , 'center'  => __('Center')
        , 'right'   => __('Right') ] ]

    , 'hover_position_vertical' =>
        [ 'display_name'    => __('Vertical placement')
        , 'description' => 'Where to place the button vertically in the image.'
        , 'type'    => 'select'
        , 'default' => 'center'
        , 'options' =>
          [ 'top'     => __('Top')
          , 'center'  => __('Center')
          , 'bottom'  => __('Bottom') ] ]
    
    , 'tags' => 
       [ 'display_name'   => __('Default hashtags')
       , 'description' => __('We will add these hashtags to the end of every image description.')
       , 'type'   => 'string'
       , 'default'=> '' ]
   ];

  /* Apply common fields to each setting in $settings. */
  foreach($settings as $key => $setting) {
    $setting['key'] = $key;
    $setting['name'] = 'apx_' . $key;
    $settings[$key] = $setting;
  }

  return $settings;
}

/* FILTER 'apx_data' */
/* Prepare data for use in JavaScript. */
function localize_variables() {
  global $post;
  $apx = [];
  
  // Admin ajax URLs.
  $apx['isAdmin'] = is_admin();
  $apx['core_version'] = APX_VERSION;
  $apx['ajaxurl'] = admin_url('admin-ajax.php');
  $apx['assetURL'] = plugins_url('advanced-pins/assets');
  
  $apx['options'] = get_plugin_settings();
  $apx['post'] = $post;
  $apx = apply_force_pin_data($post, $apx); // Setup the $apx['pin'] object.
  
  $_apx = apply_filters('apx_data', $apx);
  if (!is_array($_apx) || !array_key_exists('core_version', $_apx))
    $_apx = $apx;

  wp_localize_script('apx-admin', 'apx', $_apx);
  wp_localize_script('apx-frontend', 'apx', $_apx);
}
 