<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configure PHPMailer based on user settings.
 */
function ownomail_configure_mailer($phpmailer) {
    // Check if SMTP is enabled via settings
    $smtp_host = get_option('ownomail_smtp_host', '');

    if (!empty($smtp_host)) {
        // Use SMTP host is set
        $phpmailer->isSMTP();
        $phpmailer->Host = $smtp_host;
        $phpmailer->Port = get_option('ownomail_smtp_port', 587);
        
        // Enable authentication if username is provided
        $smtp_username = get_option('ownomail_smtp_username', '');
        $smtp_password = get_option('ownomail_smtp_password', '');
        if (!empty($smtp_username)) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp_username;
            $phpmailer->Password = $smtp_password;
        } else {
            $phpmailer->SMTPAuth = false;
        }

        // Force SSL or TLS only
        $encryption = get_option('ownomail_smtp_encryption', 'ssl');  // Default to 'ssl' if not set
        if (in_array($encryption, ['ssl', 'tls'])) {
            $phpmailer->SMTPSecure = $encryption;
        } else {
            // Force SSL if somehow an invalid option is stored
            $phpmailer->SMTPSecure = 'ssl';
        }

        // Set debug mode for SMTP if enabled in settings
        $smtp_debug = get_option('ownomail_smtp_debug', 0);
        if ($smtp_debug) {
            $phpmailer->SMTPDebug = 2;  // 2 for detailed debug
            $phpmailer->Debugoutput = 'error_log';  // Log where you can check errors
        }
    } else {
        // Fallback to default WordPress behavior using PHP mail()
        // Do not set isSMTP() to use PHP's mail() function
        $phpmailer->isMail();
    }
}
add_action('phpmailer_init', 'ownomail_configure_mailer');

/**
 * Validate and sanitize email address.
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
            'error'
        );
        return get_option('ownomail_sender_email', 'email@ownomail.com');
    }
    return $email;
}

/**
 * Validate and sanitize sender name.
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
        return get_option('ownomail_sender_name', 'Custom-made, made simple with OwnOmail');
    }
    if (mb_strlen($name) > 50) {
        add_settings_error(
            'ownomail_options_group',
            'name_too_long',
            __('⚠️ Warning: The sender name exceeded 50 characters. It was truncated.', 'ownomail'),
            'error'
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
    $name = get_option('ownomail_sender_name', 'Custom-made, made simple by OwnOmail');
    return ownomail_validate_sender_name($name);
});
