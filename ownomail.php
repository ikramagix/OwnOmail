<?php
/*
Plugin Name: OwnOmail
Plugin URI: https://github.com/ikramagix/OwnOmail
Description: Minimalist plugin to customize the sender email address and name for WordPress-generated emails.
Version: 1.4
Author: @ikramagix
Author URI: https://ikramagix.com
License: GPL2
*/

// Add filters for email customization
add_filter('wp_mail_from', 'ownomail_get_sender_email');
add_filter('wp_mail_from_name', 'ownomail_get_sender_name');

/**
 * Fetch the sender email from options
 */
function ownomail_get_sender_email() {
    $email = get_option('ownomail_sender_email', 'ownomail@example.com');
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'ownomail@example.com';
}

/**
 * Fetch the sender name from options
 */
function ownomail_get_sender_name() {
    $name = get_option('ownomail_sender_name', 'Sent with OwnOmail');
    return sanitize_text_field($name);
}

// Register settings with sanitization callbacks
function ownomail_register_settings() {
    register_setting('ownomail_options_group', 'ownomail_sender_email', [
        'sanitize_callback' => 'sanitize_email',
    ]);
    register_setting('ownomail_options_group', 'ownomail_sender_name', [
        'sanitize_callback' => 'ownomail_sanitize_name',
    ]);
}
add_action('admin_init', 'ownomail_register_settings');

/**
 * Custom sanitize callback for sender name
 */
function ownomail_sanitize_name($name) {
    $name = sanitize_text_field($name);
    $name = trim($name); // Remove extra spaces
    if (strlen($name) > 50) {
        $name = substr($name, 0, 50); // Restrict to 50 characters
    }
    return $name;
}

// Add admin menu
function ownomail_add_admin_menu() {
    add_menu_page(
        esc_html__('OwnOmail Settings', 'ownomail'),
        esc_html__('OwnOmail', 'ownomail'),
        'manage_options',
        'ownomail',
        'ownomail_settings_page',
        'dashicons-buddicons-pm',
        100
    );
}
add_action('admin_menu', 'ownomail_add_admin_menu');

/**
 * Admin settings page
 */
function ownomail_settings_page() {
    // Generate nonce
    $nonce_action = 'ownomail_settings_action';
    $nonce        = wp_create_nonce($nonce_action);

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('OwnOmail Settings', 'ownomail'); ?></h1>

        <!-- Display admin notices (errors, warnings, success) -->
        <?php settings_errors('ownomail_settings'); ?>

        <form method="post" action="admin-post.php">
            <input type="hidden" name="action" value="ownomail_save_settings" />
            <input type="hidden" name="ownomail_nonce" value="<?php echo esc_attr($nonce); ?>">

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_sender_email">
                            <?php esc_html_e('Sender Email', 'ownomail'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="email"
                            id="ownomail_sender_email"
                            name="ownomail_sender_email"
                            value="<?php echo esc_attr(get_option('ownomail_sender_email', 'ownomail@example.com')); ?>"
                            required
                            maxlength="100"
                        />
                        <p class="description">
                            <?php esc_html_e('Enter the email address that will appear as the sender in WordPress emails.', 'ownomail'); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="ownomail_sender_name">
                            <?php esc_html_e('Sender Name', 'ownomail'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="ownomail_sender_name"
                            name="ownomail_sender_name"
                            value="<?php echo esc_attr(get_option('ownomail_sender_name', 'OwnOmail Powered')); ?>"
                            required
                            maxlength="50"
                        />
                        <p class="description">
                            <?php esc_html_e('Enter the name that will appear as the sender in WordPress emails. Maximum 50 characters.', 'ownomail'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Save settings securely and display notices
 */
add_action('admin_post_ownomail_save_settings', 'ownomail_save_settings');
function ownomail_save_settings() {
    // Verify the nonce
    if (!isset($_POST['ownomail_nonce']) || 
        !wp_verify_nonce($_POST['ownomail_nonce'], 'ownomail_settings_action')) {
        add_settings_error(
            'ownomail_settings',
            'invalid_nonce',
            __('Invalid request. Could not verify the token.', 'ownomail'),
            'error'
        );
        wp_redirect(admin_url('admin.php?page=ownomail'));
        exit;
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        add_settings_error(
            'ownomail_settings',
            'unauthorized',
            __('You do not have permission to perform this action.', 'ownomail'),
            'error'
        );
        wp_redirect(admin_url('admin.php?page=ownomail'));
        exit;
    }

    // 1) Process email
    if (isset($_POST['ownomail_sender_email'])) {
        $email = sanitize_email($_POST['ownomail_sender_email']);

        // If valid, update option; otherwise show a 'warning'
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            update_option('ownomail_sender_email', $email);

            // Success: new email address changed
            add_settings_error(
                'ownomail_settings',
                'email_updated',
                __('The new email address has been successfully changed.', 'ownomail'),
                'updated'
            );
        } else {
            // Warning: user can fix it
            add_settings_error(
                'ownomail_settings',
                'invalid_email',
                __('Please enter a valid email address.', 'ownomail'),
                'warning'
            );
        }
    }

    // 2) Process name
    if (isset($_POST['ownomail_sender_name'])) {
        $raw_name       = sanitize_text_field($_POST['ownomail_sender_name']);
        $sanitized_name = ownomail_sanitize_name($raw_name);

        update_option('ownomail_sender_name', $sanitized_name);

        // If truncation or other fix occurred, show a warning; else success.
        if (strlen($raw_name) > 50) {
            add_settings_error(
                'ownomail_settings',
                'name_truncated',
                __('The name was too long and has been truncated to 50 characters.', 'ownomail'),
                'warning'
            );
        } else {
            add_settings_error(
                'ownomail_settings',
                'name_updated',
                __('Sender name updated successfully.', 'ownomail'),
                'updated'
            );
        }
    }

    // Redirect back to the page (no &settings-updated).
    wp_redirect(admin_url('admin.php?page=ownomail'));
    exit;
}

// Add sanitization filters to options
add_filter('sanitize_option_ownomail_sender_email', 'sanitize_email');
add_filter('sanitize_option_ownomail_sender_name', 'ownomail_sanitize_name');