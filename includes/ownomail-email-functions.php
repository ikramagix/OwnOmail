<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate and sanitize email address.
 *
 * @param string $email The email address to validate.
 * @return string The sanitized email if valid; previous value if invalid.
 */
function ownomail_validate_sender_email($email) {
    $email = sanitize_email($email);
    
    if (empty($email)) {
        add_settings_error(
            'ownomail_options_group',
            'empty_email',
            __('❌ Error: The sender email cannot be empty.', 'ownomail'),
            'error'
        );
        return get_option('ownomail_sender_email', 'email@ownomail.com');
    }

    if (!is_email($email)) {
        add_settings_error(
            'ownomail_options_group',
            'invalid_email',
            __('⚠️ Warning: The email format is invalid. Using previous value.', 'ownomail'),
            'warning'
        );
        return get_option('ownomail_sender_email', 'email@ownomail.com');
    }

    return $email;
}

/**
 * Validate and sanitize sender name.
 *
 * @param string $name The sender name to validate.
 * @return string The sanitized name, truncated to 50 characters if necessary.
 */
function ownomail_validate_sender_name($name) {
    $name = sanitize_text_field($name);
    
    if (empty($name)) {
        add_settings_error(
            'ownomail_options_group',
            'empty_name',
            __('❌ Error: The sender name cannot be empty.', 'ownomail'),
            'error'
        );
        return get_option('ownomail_sender_name', 'OwnOmail Sender');
    }

    if (mb_strlen($name) > 50) {
        add_settings_error(
            'ownomail_options_group',
            'name_too_long',
            __('⚠️ Warning: The sender name exceeded 50 characters. It was truncated.', 'ownomail'),
            'warning'
        );
        return mb_substr($name, 0, 50);
    }

    return $name;
}

// Modify "From" email address
add_filter('wp_mail_from', function($original_email_address) {
    $email = get_option('ownomail_sender_email', 'email@ownomail.com');
    return ownomail_validate_sender_email($email);
});

// Modify "From" name
add_filter('wp_mail_from_name', function($original_email_from) {
    $name = get_option('ownomail_sender_name', 'OwnOmail Sender');
    return ownomail_validate_sender_name($name);
});
