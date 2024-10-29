<?php namespace apx ?>
<div id="apx-logo-wrap">
    <img id="apx-logo" src="<?= asset('logo.png') ?>" />
</div>
<?php do_action('apx_settings_page_before') ?>

<div id="apx-settings-page" class="wrap">
    <div id="apx-general-settings" class="apx-col-75">
            
        <div id="apx-title-box"></div>
        <form method="post" action="options.php">
            <?= settings_fields('apx'); ?>
            <?= do_settings_sections('apx') ?>
            <?= submit_button() ?>
        </form>

        <!-- Rendered in settings-page.php to be placed in DOM into the correct parent <td> by Javascript in settings-page.js -->
        <div class="apx-template">
          <button id="apx_upload_logo">Update Logo</button>
        </div>

    </div>

    <div class="apx-col-25">
        <img id="apx-hover-preview" src="<?= asset('placeholder.jpg') ?>" />
    </div>
</div>


<?php do_action('apx_settings_page_after') ?>
<div id="apx-footer">
    <h3>Need to tell us something?</h3>
    <a id="apx-contact-us" target="_blank" href="https://advancedpins.com/submit-ticket">Come on over, we're listening</a>
</div>