<?php

function flushSettingsToFiles() {
    \LogHero\Wordpress\LogHero_Plugin::refreshAPISettings();
    \LogHero\Wordpress\LogHeroGlobals::Instance()->refreshAPIKey(get_option('api_key'));
}
flushSettingsToFiles();

function loghero_options_page()
{
    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <h1>LogHero Options</h1>
        <form method="post" action="options.php">
            <?php

            //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
            settings_fields('loghero');

            // all the add_settings_field callbacks is displayed here
            do_settings_sections('loghero');

            // Add the submit button to serialize the options
            submit_button();

            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'loghero_admin_init');
function loghero_admin_init() {
    $settings_group = 'loghero';
    $setting_name = 'api_key';

    $settings_section = 'loghero_main';
    $page = $settings_group;
    add_settings_section(
        $settings_section,
        'Client Setup',
        '', # TODO Add help text with this column
        $page
    );

    // Add fields to that section
    add_settings_field(
        $setting_name,
        'LogHero API Key (required)',
        'loghero_api_key_input_renderer',
        $page,
        $settings_section
    );

    register_setting($page, $setting_name); # TODO Use sanitize callback
}

function loghero_api_key_input_renderer() {
    ?>
    <input type="text" name="api_key" id="api_key" value="<?php echo get_option('api_key'); ?>" />
    <?php
}

add_action('admin_menu', 'loghero_admin_add_page');
function loghero_admin_add_page() {
    add_options_page(
        'LogHero Settings',
        'LogHero',
        'manage_options',
        'loghero',
        'loghero_options_page'
    );
}



function setup_api_key_admin_notice(){
    $currentApiKey = get_option('api_key');
    if (!$currentApiKey) {
        echo '<div class="notice notice-warning is-dismissible">
                 <p>Your LogHero API key is not setup. Please go to the <a href="/wp-admin/options-general.php?page=loghero">LogHero settings page</a> and enter the API key retrieved from <a target="_blank" href="https://log-hero.com">log-hero.com</a>.</p>
             </div>';
    }
    // TODO: Put this in a function and add test case
    $asyncFlushErrorFile = LogHero\Wordpress\LogHeroGlobals::Instance()->getErrorFilename('async-flush');
    if (file_exists($asyncFlushErrorFile)) {
        $errorMessage = fgets(fopen($asyncFlushErrorFile, 'r'));
        echo '<div class="notice notice-warning is-dismissible">
                 <p>LogHero asynchronous flush failed! This is most likely caused by your server configuration which might block requests made from your backend. The log events are currently flushed synchronously. For more information visit <a target="_blank" href="https://log-hero.com/issues/async-flush-failed">log-hero.com/issues/async-flush-failed</a>.</p><p> Error message: ' . $errorMessage . '</p>
             </div>';
    }
}
add_action('admin_notices', 'setup_api_key_admin_notice');

require_once __DIR__ . '/loghero-admin-dev.php';