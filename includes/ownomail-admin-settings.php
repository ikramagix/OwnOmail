<?php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

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
    register_setting('ownomail_options_group', 'ownomail_email_format', [
        'sanitize_callback' => 'ownomail_sanitize_email_format',
    ]);
    register_setting('ownomail_options_group', 'ownomail_use_smtp');
    register_setting('ownomail_options_group', 'ownomail_smtp_host');
    register_setting('ownomail_options_group', 'ownomail_smtp_port');
    register_setting('ownomail_options_group', 'ownomail_smtp_username');
    register_setting('ownomail_options_group', 'ownomail_smtp_password');
    register_setting('ownomail_options_group', 'ownomail_smtp_encryption');
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
        'dashicons-email-alt',
        100
    );
}
add_action('admin_menu', 'ownomail_add_admin_menu');

/**
 * Handle form submissions for each section.
 */
function ownomail_handle_form_submission() {
    if (!empty($_POST['ownomail_action'])) {
        check_admin_referer('ownomail_settings_action', 'ownomail_settings_nonce');

        switch ($_POST['ownomail_action']) {
            case 'update_sender_email':
                if (update_option('ownomail_sender_email', sanitize_email($_POST['ownomail_sender_email']))) {
                    add_settings_error('ownomail_options_group', 'sender_email_updated', __('✅ Sender email updated.', 'ownomail'), 'success');
                } else {
                    add_settings_error('ownomail_options_group', 'sender_email_failed', __('❌ Failed to update sender email.', 'ownomail'), 'error');
                }
                break;

            case 'update_sender_name':
                if (update_option('ownomail_sender_name', sanitize_text_field($_POST['ownomail_sender_name']))) {
                    add_settings_error('ownomail_options_group', 'sender_name_updated', __('✅ Sender name updated.', 'ownomail'), 'success');
                } else {
                    add_settings_error('ownomail_options_group', 'sender_name_failed', __('❌ Failed to update sender name.', 'ownomail'), 'error');
                }
                break;

            case 'update_email_format':
                if (update_option('ownomail_email_format', $_POST['ownomail_email_format'])) {
                    add_settings_error('ownomail_options_group', 'email_format_updated', __('✅ Email format updated.', 'ownomail'), 'success');
                } else {
                    add_settings_error('ownomail_options_group', 'email_format_failed', __('❌ Failed to update email format.', 'ownomail'), 'error');
                }
                break;

            case 'update_smtp_settings':
                $success = update_option('ownomail_use_smtp', isset($_POST['ownomail_use_smtp'])) &&
                           update_option('ownomail_smtp_host', sanitize_text_field($_POST['ownomail_smtp_host'])) &&
                           update_option('ownomail_smtp_port', intval($_POST['ownomail_smtp_port'])) &&
                           update_option('ownomail_smtp_username', sanitize_text_field($_POST['ownomail_smtp_username'])) &&
                           update_option('ownomail_smtp_password', sanitize_text_field($_POST['ownomail_smtp_password'])) &&
                           update_option('ownomail_smtp_encryption', sanitize_text_field($_POST['ownomail_smtp_encryption']));

                if ($success) {
                    add_settings_error('ownomail_options_group', 'smtp_settings_updated', __('✅ SMTP settings updated successfully.', 'ownomail'), 'success');
                } else {
                    add_settings_error('ownomail_options_group', 'smtp_settings_failed', __('❌ Failed to update SMTP settings.', 'ownomail'), 'error');
                }
                break;
        }
    }
}
add_action('admin_post_ownomail_save_settings', 'ownomail_handle_form_submission');

/**
 * Render the OwnOmail settings page with separate forms.
 */
function ownomail_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('OwnOmail Settings', 'ownomail'); ?></h1>
        
        <!-- Sender Email -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('ownomail_settings_action', 'ownomail_settings_nonce'); ?>
            <input type="hidden" name="action" value="ownomail_save_settings">
            <input type="hidden" name="ownomail_action" value="update_sender_email">
            <label>Sender Email:</label>
            <input type="email" name="ownomail_sender_email" value="<?php echo esc_attr(get_option('ownomail_sender_email', '')); ?>">
            <input type="submit" value="Update Email">
        </form>

        <!-- Sender Name -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="ownomail_save_settings">
            <input type="hidden" name="ownomail_action" value="update_sender_name">
            <label>Sender Name:</label>
            <input type="text" name="ownomail_sender_name" value="<?php echo esc_attr(get_option('ownomail_sender_name', '')); ?>">
            <input type="submit" value="Update Name">
        </form>

        <!-- Email Format -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="ownomail_save_settings">
            <input type="hidden" name="ownomail_action" value="update_email_format">
            <label>Email Format:</label>
            <select name="ownomail_email_format">
                <option value="html" <?php selected(get_option('ownomail_email_format'), 'html'); ?>>HTML</option>
                <option value="text" <?php selected(get_option('ownomail_email_format'), 'text'); ?>>Text</option>
            </select>
            <input type="submit" value="Update Format">
        </form>
        
        <!-- SMTP Settings -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="ownomail_save_settings">
            <input type="hidden" name="ownomail_action" value="update_smtp_settings">
            <label>SMTP Host:</label>
            <input type="text" name="ownomail_smtp_host" value="<?php echo esc_attr(get_option('ownomail_smtp_host', '')); ?>">
            <label>SMTP Port:</label>
            <input type="number" name="ownomail_smtp_port" value="<?php echo esc_attr(get_option('ownomail_smtp_port', '587')); ?>">
            <input type="submit" value="Update SMTP Settings">
        </form>
    </div>
    <?php
}
