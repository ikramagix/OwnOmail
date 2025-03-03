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
        echo '<div class="notice notice-success is-dismissible">
        <p>' . esc_html__('🎉 OwnOmail is now activated! Visit the OwnOmail settings page to start personalizing your email experience with custom sender information, formats, SMTP settings and much more to come. Let\'s make it yours.', 'ownomail') . '</p>
        <p><a href="' . esc_url(admin_url('admin.php?page=ownomail')) . '" class="button button-success">Take me there</a></p>
    </div>';            
    // Remove notice after display.
        delete_option('ownomail_activation_notice');
    }
}
add_action('admin_notices', 'ownomail_activation_admin_notice');

// Enqueue Bootstrap
function ownomail_enqueue_bootstrap($hook) {
    // Load Bootstrap ONLY on OwnOmail's settings page
    if ($hook !== 'toplevel_page_ownomail') {
        return;
    }
    // Now enqueue
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'ownomail_enqueue_bootstrap');
