<?php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/**
 * Show an admin notice if mbstring is missing.
 */
function ownomail_admin_notice_mbstring() {
    if (!extension_loaded('mbstring')) {
        echo '<div class="error"><p><strong>OwnOmail Error:</strong> The PHP <code>mbstring</code> extension is missing. Please install it or ask your host to enable it.</p></div>';
    }
}
add_action('admin_notices', 'ownomail_admin_notice_mbstring');

/**
 * Show a notice if no SMTP relay is detected.
 */
function ownomail_admin_notice_smtp() {
    if (!ownomail_detect_smtp()) {
        echo '<div class="notice notice-warning"><p><strong>OwnOmail Notice:</strong> 
        No SMTP relay detected. Emails will be sent using PHP’s default mailer.
        OwnOmail will apply your settings and will be functional. NOTICE: Emails sent from your website could potentially be falsely detected as spam and/or placed in the junk folder. We recommend requesting a test email to make sure it doesn't.</p></div>';
    }
}
add_action('admin_notices', 'ownomail_admin_notice_smtp');

/**
 * Register plugin settings with validation callbacks.
 */
function ownomail_register_settings() {
    register_setting('ownomail_options_group', 'ownomail_sender_email', [
        'sanitize_callback' => 'ownomail_validate_sender_email',
    ]);
    register_setting('ownomail_options_group', 'ownomail_sender_name', [
        'sanitize_callback' => 'ownomail_validate_sender_name',
    ]);

    // Check for a successful settings update
    if (!empty($_POST) && empty(get_settings_errors('ownomail_options_group'))) {
        add_settings_error(
            'ownomail_options_group',
            'settings_saved',
            __('✅ OwnOmail settings have been saved successfully.', 'ownomail'),
            'success'
        );
    }
}
add_action('admin_init', 'ownomail_register_settings');

/**
 * Display admin notices for settings updates.
 */
function ownomail_admin_notices() {
    settings_errors('ownomail_options_group');
}
add_action('admin_notices', 'ownomail_admin_notices');

/**
 * Add settings page to the admin menu.
 */
function ownomail_add_admin_menu() {
    add_menu_page(
        __('OwnOmail Settings', 'ownomail'),
        __('OwnOmail', 'ownomail'),
        'manage_options',
        'ownomail',
        'ownomail_settings_page',
        'dashicons-buddicons-pm',
        100
    );
}
add_action('admin_menu', 'ownomail_add_admin_menu');

/**
 * Render the settings page.
 */
function ownomail_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('OwnOmail Plugin Settings', 'ownomail'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ownomail_options_group');
            do_settings_sections('ownomail');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_sender_email"><?php esc_html_e('Sender Email', 'ownomail'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="ownomail_sender_email" name="ownomail_sender_email"
                               value="<?php echo esc_attr(get_option('ownomail_sender_email', 'email@ownomail.com')); ?>"
                               required maxlength="100"/>
                        <p class="description"><?php esc_html_e('Enter the email address that will appear as the sender in WordPress emails.', 'ownomail'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_sender_name"><?php esc_html_e('Sender Name', 'ownomail'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ownomail_sender_name" name="ownomail_sender_name"
                               value="<?php echo esc_attr(get_option('ownomail_sender_name', 'OwnOmail Sender')); ?>"
                               required maxlength="50"/>
                        <p class="description"><?php esc_html_e('Enter the name that will appear as the sender in WordPress emails. Maximum 50 characters.', 'ownomail'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
