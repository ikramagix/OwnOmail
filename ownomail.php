<?php
/*
Plugin Name: OwnOmail
Plugin URI: https://github.com/ikramagix/OwnOmail
Description: Minimalist plugin to customize the sender email address and name for WordPress-generated emails.
Version: 1.0
Author: @ikramagix
Author URI: https://ikramagix.com
License: AGPLv3
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load functionalities
require_once plugin_dir_path(__FILE__) . 'includes/ownomail-email-functions.php';

// Load admin settings if in admin area
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/ownomail-admin-settings.php';
}

// Activation hook – run when plugin is activated.
function ownomail_activate() {
    // Set an option to show an activation alert on the next admin page load.
    update_option('ownomail_activation_notice', true);
}
register_activation_hook(__FILE__, 'ownomail_activate');

// Admin notice after activation.
function ownomail_activation_admin_notice() {
    if ( get_option('ownomail_activation_notice') ) {
        echo '<div class="notice notice-warning is-dismissible">
            <p>' . esc_html__('OwnOmail is activated. Please go to the OwnOmail settings page and configure your SMTP settings (otherwise emails will not work as expected).', 'ownomail') . '</p>
        </div>';
        // Remove notice after display.
        delete_option('ownomail_activation_notice');
    }
}
add_action('admin_notices', 'ownomail_activation_admin_notice');

// Enqueue Bootstrap CSS and JS for admin pages
function ownomail_enqueue_bootstrap() {
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'ownomail_enqueue_bootstrap');
