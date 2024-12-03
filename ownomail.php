<?php
/*
Plugin Name: OwnOmail
Plugin URI: https://github.com/ikramagix/OwnOmail
Description: Minimalist plugin to customize the sender email address and name for WordPress-generated emails.
Version: 1.0
Author: @ikramagix
Author URI: https://ikramagix.com
License: GPL2
*/

// Add filters for email customization
add_filter('wp_mail_from', 'ownomail_get_sender_email');
add_filter('wp_mail_from_name', 'ownomail_get_sender_name');

// Fetch the sender email from options
function ownomail_get_sender_email() {
    return get_option('ownomail_sender_email', 'ownomail@example.com'); // Default email if not set.
}

// Fetch the sender name from options
function ownomail_get_sender_name() {
    return get_option('ownomail_sender_name', 'OwnOmail Powered'); // Default name if not set.
}

// Register settings
function ownomail_register_settings() {
    add_option('ownomail_sender_email', 'ownomail@example.com');
    add_option('ownomail_sender_name', 'Ask your Admin about OwnOmail');
    register_setting('ownomail_options_group', 'ownomail_sender_email');
    register_setting('ownomail_options_group', 'ownomail_sender_name');
}
add_action('admin_init', 'ownomail_register_settings');

// Add a menu for the plugin
function ownomail_add_admin_menu() {
    add_menu_page(
        'OwnOmail Settings',
        'OwnOmail',
        'manage_options',
        'ownomail',
        'ownomail_settings_page',
        'dashicons-buddicons-pm', // Icon for the menu
        100 // Position in the menu
    );
}
add_action('admin_menu', 'ownomail_add_admin_menu');

// Admin settings page
function ownomail_settings_page() {
?>
    <div class="wrap">
        <h1>OwnOmail Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('ownomail_options_group'); ?>
            <?php do_settings_sections('ownomail_options_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="ownomail_sender_email">Sender Email</label></th>
                    <td><input type="email" id="ownomail_sender_email" name="ownomail_sender_email" value="<?php echo esc_attr(get_option('ownomail_sender_email', 'default@example.com')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="ownomail_sender_name">Sender Name</label></th>
                    <td><input type="text" id="ownomail_sender_name" name="ownomail_sender_name" value="<?php echo esc_attr(get_option('ownomail_sender_name', 'Your Website Name')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
