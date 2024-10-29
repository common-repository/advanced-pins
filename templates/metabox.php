<?php 
namespace apx;


function get_image_preview_src() {
  if (isset($_POST['apx_pinterest_image_id'])) {
    $image_id = sanitize_text_field($_POST['apx_pinterest_image_id']);
    $img_data = wp_get_attachment_image_src($image_id, 'full');
    $src = $img_data[0];
  }
  else {
    $src = '';
  }

  return $src;
}

function get_pinterest_description() {
  if (isset($_POST['apx_description'])) 
    $description = sanitize_text_field($_POST['apx_description']);
  else 
    $description = '';

  return $description;
}

// Shorthand for printing HTML attributes from db data.
function name_value($key) {
  $value = val($key); 

  return "name=\"apx_{$key}\" value=\"{$value}\"";
}

// Fetch the value from POST data for $key.
function val($key) {
  if (isset($_POST["apx_$key"]))
    $value = sanitize_text_field($_POST["apx_$key"]);
  else 
    $value = get_option($key, '');

  return $value;
}

function selected_value($value, $key) {
  $v = val($key);
  $selected = \selected($value, $v);

  return "{$selected} value='{$value}'";
}

function option($name, $value, $text) {
  $attributes = selected_value($value, $name);

  return "<option $attributes>$text</option>";
}
?>

<!-- Begin Advanced Pins Metabox -->
<div id="apx-metabox">
  <div id="pinterest-image-settings">

      <div class="apx-meta-field">
          <h3>Force pin an image or show the Pin Gallery?</h3>
        <select <?= name_value('image_source') ?>>
          <?= option('image_source', 'force', __('Force Pin an Image', 'apx')) ?>
          <?= option('image_source', 'gallery', __('Show the Pin Gallery', 'apx')) ?>
        </select>
      </div>

      <div id="apx-force-pinterest-image" class="apx-description-box" >
        <button id="apx-update-image-source">Update image</button>
        <input type="hidden" name="apx_pinterest_image_id" id="apx-pinterest-image" />

        <img id="apx-pinterest-image-preview" src="<?= get_image_preview_src() ?>"/>
      </div>

      <div class="apx-meta-field apx-description-box"> 
        <h3>Force a Pinterest Description?</h3>
        <label for="apx_description">Force Pin Description</label>
        <textarea name="apx_description"><?= get_pinterest_description() ?></textarea>
        <p>If you want to use the same description for every image on the page, enter it here.</p>
      </div>

  </div> 
</div> 