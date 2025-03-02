<?php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/**
 * Register plugin settings with validation callbacks.
 */
function ownomail_register_settings() {
    register_setting('ownomail_options_group', 'ownomail_sender_email');
    register_setting('ownomail_options_group', 'ownomail_sender_name');
    register_setting('ownomail_options_group', 'ownomail_email_format');
    
    // Outgoing (SMTP)
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
        'dashicons-buddicons-pm',
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
 * Render the OwnOmail settings page with Bootstrap.
 */
function ownomail_settings_page() {
    ?>
    <div class="wrap container my-5">
        <h1 class="mb-4"><?php esc_html_e('OwnOmail Settings', 'ownomail'); ?></h1>
        
        <!-- Sender Information -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Sender Information</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('ownomail_settings_action', 'ownomail_settings_nonce'); ?>
                    <input type="hidden" name="action" value="ownomail_save_settings">
                    <input type="hidden" name="ownomail_action" value="update_sender_email">

                    <div class="form-group">
                        <label for="sender_email">Sender Email</label>
                        <input type="email" class="form-control" id="sender_email" name="ownomail_sender_email"
                               value="<?php echo esc_attr(get_option('ownomail_sender_email', '')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="sender_name">Sender Name</label>
                        <input type="text" class="form-control" id="sender_name" name="ownomail_sender_name"
                               value="<?php echo esc_attr(get_option('ownomail_sender_name', '')); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Sender Info</button>
                </form>
            </div>
        </div>

        <!-- Email Format (HTML or Text) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Email Format</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="ownomail_save_settings">
                    <input type="hidden" name="ownomail_action" value="update_email_format">

                    <div class="form-group">
                        <label for="email_format">Email Format</label>
                        <select id="email_format" name="ownomail_email_format" class="form-control">
                            <option value="html" <?php selected(get_option('ownomail_email_format'), 'html'); ?>>HTML</option>
                            <option value="text" <?php selected(get_option('ownomail_email_format'), 'text'); ?>>Text</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Email Format</button>
                </form>
            </div>
        </div>

        <!-- SMTP Settings (Outgoing) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Outgoing Mail (SMTP)</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="ownomail_save_settings">
                    <input type="hidden" name="ownomail_action" value="update_smtp_settings">

                    <div class="form-group form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="use_smtp" name="ownomail_use_smtp" value="1"
                               <?php checked(1, get_option('ownomail_use_smtp', 0)); ?>>
                        <label class="form-check-label" for="use_smtp">Use SMTP for sending emails</label>
                    </div>

                    <div class="form-group">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" class="form-control" id="smtp_host" name="ownomail_smtp_host"
                               value="<?php echo esc_attr(get_option('ownomail_smtp_host', '')); ?>" placeholder="mail.example.com">
                    </div>

                    <div class="form-group">
                        <label for="smtp_port">SMTP Port</label>
                        <input type="number" class="form-control" id="smtp_port" name="ownomail_smtp_port"
                               value="<?php echo esc_attr(get_option('ownomail_smtp_port', '465')); ?>" placeholder="465 or 587">
                        <small class="form-text text-muted">Typically 465 (SSL) or 587 (TLS).</small>
                    </div>

                    <div class="form-group">
                        <label for="smtp_username">SMTP Username</label>
                        <input type="text" class="form-control" id="smtp_username" name="ownomail_smtp_username"
                               value="<?php echo esc_attr(get_option('ownomail_smtp_username', '')); ?>" placeholder="project@example.eu">
                    </div>

                    <div class="form-group">
                        <label for="smtp_password">SMTP Password</label>
                        <input type="password" class="form-control" id="smtp_password" name="ownomail_smtp_password"
                               value="<?php echo esc_attr(get_option('ownomail_smtp_password', '')); ?>" placeholder="Your Email Password">
                    </div>

                    <div class="form-group">
                        <label for="smtp_encryption">Encryption</label>
                        <select id="smtp_encryption" name="ownomail_smtp_encryption" class="form-control">
                            <option value="ssl" <?php selected(get_option('ownomail_smtp_encryption'), 'ssl'); ?>>SSL</option>
                            <option value="tls" <?php selected(get_option('ownomail_smtp_encryption'), 'tls'); ?>>TLS</option>
                        </select>
                        <small class="form-text text-muted">Required to secure your connection.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Save SMTP Settings</button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
