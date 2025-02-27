<?php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/**
 * Register plugin settings with validation callbacks.
 */
function ownomail_register_settings() {
    // Existing settings
    register_setting('ownomail_options_group', 'ownomail_sender_email', [
        'sanitize_callback' => 'ownomail_validate_sender_email',
    ]);
    register_setting('ownomail_options_group', 'ownomail_sender_name', [
        'sanitize_callback' => 'ownomail_validate_sender_name',
    ]);

    // New "Email Format" option (HTML or Text)
    register_setting('ownomail_options_group', 'ownomail_email_format', [
        'sanitize_callback' => 'ownomail_sanitize_email_format',
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
 * Sanitize the email format setting.
 *
 * @param string $value The user-selected format.
 * @return string 'html' or 'text'
 */
function ownomail_sanitize_email_format($value) {
    // Default to 'html' if an unexpected value is passed
    return ($value === 'text') ? 'text' : 'html';
}

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
 * Render the OwnOmail settings page with additional user-friendly elements.
 */
function ownomail_settings_page() {
    // Handle Test Email submission
    if (isset($_POST['ownomail_test_email_submit'])) {
        check_admin_referer('ownomail_test_email_action', 'ownomail_test_email_nonce');

        $test_email = sanitize_email($_POST['ownomail_test_email']);
        if (empty($test_email)) {
            add_settings_error(
                'ownomail_options_group',
                'test_email_empty',
                __('Error: Please enter a valid email address for testing.', 'ownomail'),
                'error'
            );
        } else {
            // Determine the format and build the email accordingly
            $email_format = get_option('ownomail_email_format', 'html'); // 'html' or 'text'
            $subject = __('OwnOmail Test Email', 'ownomail');

            if ($email_format === 'html') {
                // HTML email example
                $body    = '<div style="font-family: Arial, sans-serif; line-height: 1.5;">'
                         . '<h2 style="color: #0073aa; margin-top: 0;">Hello from OwnOmail!</h2>'
                         . '<p>This is a <strong>test email</strong> confirming that your WordPress email settings are configured correctly.</p>'
                         . '<p>If you can read this message in HTML format, everything is set up right!</p>'
                         . '<p><em>Custom-made. Made simple.</em></p>'
                         . '</div>';
                $headers = ['Content-Type: text/html; charset=UTF-8'];
            } else {
                // Plain text email example
                $body    = "Hello from OwnOmail!\n\n"
                         . "This is a test email in plain text format confirming that your WordPress email settings are working.\n"
                         . "If you see this message clearly, everything is good!\n\n"
                         . "Custom-made. Made simple.\n";
                $headers = []; // WP defaults to plain text
            }

            // Attempt to send the test email
            $sent = wp_mail($test_email, $subject, $body, $headers);

            if ($sent) {
                add_settings_error(
                    'ownomail_options_group',
                    'test_email_success',
                    sprintf(__('Test email successfully sent to %s.', 'ownomail'), esc_html($test_email)),
                    'success'
                );
            } else {
                add_settings_error(
                    'ownomail_options_group',
                    'test_email_failure',
                    sprintf(__('Error: Test email could not be sent to %s.', 'ownomail'), esc_html($test_email)),
                    'error'
                );
            }
        }
    }

    // Detect whether SMTP is available
    $smtp_detected = ownomail_detect_smtp();
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('OwnOmail Plugin Settings', 'ownomail'); ?></h1>
        
        <!-- Show current SMTP detection status -->
        <?php if ($smtp_detected): ?>
            <div class="notice notice-success">
                <p><?php _e('SMTP Relay Detected. Your emails should be sent via SMTP.', 'ownomail'); ?></p>
            </div>
        <?php else: ?>
            <div class="notice notice-warning">
                <p><?php _e('No SMTP Relay Detected. Your emails will fall back to PHP’s default mail() function.', 'ownomail'); ?></p>
            </div>
        <?php endif; ?>

        <!-- Main Settings Form -->
        <form method="post" action="options.php" style="margin-bottom: 20px;">
            <?php
            // WP Settings API hooks
            settings_fields('ownomail_options_group');
            do_settings_sections('ownomail');
            ?>
            <table class="form-table">
                <!-- Sender Email -->
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_sender_email"><?php esc_html_e('Sender Email', 'ownomail'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="ownomail_sender_email" name="ownomail_sender_email"
                               value="<?php echo esc_attr(get_option('ownomail_sender_email', 'email@ownomail.com')); ?>"
                               required maxlength="100"/>
                        <p class="description">
                            <?php esc_html_e('Enter the email address that will appear as the sender in WordPress emails.', 'ownomail'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Sender Name -->
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_sender_name"><?php esc_html_e('Sender Name', 'ownomail'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ownomail_sender_name" name="ownomail_sender_name"
                               value="<?php echo esc_attr(get_option('ownomail_sender_name', 'OwnOmail Sender')); ?>"
                               required maxlength="50"/>
                        <p class="description">
                            <?php esc_html_e('Enter the name that will appear as the sender in WordPress emails. Maximum 50 characters.', 'ownomail'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Email Format (HTML or Text) -->
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_email_format"><?php esc_html_e('Email Format', 'ownomail'); ?></label>
                    </th>
                    <td>
                        <?php
                        $saved_format = get_option('ownomail_email_format', 'html');
                        ?>
                        <select id="ownomail_email_format" name="ownomail_email_format">
                            <option value="html" <?php selected($saved_format, 'html'); ?>>
                                <?php esc_html_e('HTML', 'ownomail'); ?>
                            </option>
                            <option value="text" <?php selected($saved_format, 'text'); ?>>
                                <?php esc_html_e('Plain Text', 'ownomail'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Choose how emails are formatted when sent. HTML allows rich formatting; Plain Text is simpler.', 'ownomail'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Settings', 'ownomail')); ?>
        </form>

        <!-- Test Email Form -->
        <hr/>
        <h2><?php esc_html_e('Hello from OwnOmail! ⭐', 'ownomail'); ?></h2>
        <p><?php esc_html_e('Send a quick test email to confirm everything is working!', 'ownomail'); ?></p>
        <form method="post">
            <?php wp_nonce_field('ownomail_test_email_action', 'ownomail_test_email_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_test_email"><?php esc_html_e('Test Email Address', 'ownomail'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="ownomail_test_email" name="ownomail_test_email" value=""
                               placeholder="<?php esc_attr_e('yourname@ownomail.com', 'ownomail'); ?>" />
                        <p class="description">
                            <?php esc_html_e('Enter the recipient address for the test email.', 'ownomail'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <input type="submit" name="ownomail_test_email_submit" class="button button-secondary" 
                   value="<?php esc_attr_e('Send Test Email', 'ownomail'); ?>" />
        </form>
    </div>
    <?php
}
