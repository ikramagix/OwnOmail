<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate and sanitize email address.
 *
 * @param string $email The email address to validate.
 * @return string The sanitized email if valid; default email otherwise.
 */
function ownomail_validate_sender_email($email) {
    $email = sanitize_email($email);
    return is_email($email) ? $email : 'email@ownomail.com';
}

/**
 * Validate and sanitize sender name.
 *
 * @param string $name The sender name to validate.
 * @return string The sanitized name, truncated to 50 characters if necessary.
 */
function ownomail_validate_sender_name($name) {
    $name = sanitize_text_field($name);
    return mb_strimwidth($name, 0, 50);
}

// Modify "From" email address
add_filter('wp_mail_from', function($original_email_address) {
    $email = get_option('ownomail_sender_email', 'email@ownomail.com');
    return ownomail_validate_sender_email($email);
});

// Modify "From" name
add_filter('wp_mail_from_name', function($original_email_from) {
    $name = get_option('ownomail_sender_name', 'Custom-made, made simple—thanks to OwnOmail');
    return ownomail_validate_sender_name($name);
});
