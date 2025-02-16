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

// Function to check if mbstring is available
function ownomail_check_mbstring() {
    if (!extension_loaded('mbstring')) {
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate plugin
        wp_die(
            'The <strong>mbstring</strong> PHP extension is missing! This plugin requires mbstring to work. 
            Please contact your hosting provider or install it.',
            'Plugin Error',
            array('back_link' => true)
        );
    }
}
register_activation_hook(__FILE__, 'ownomail_check_mbstring');

// Admin notice if mbstring is missing
function ownomail_mbstring_admin_notice() {
    if (!extension_loaded('mbstring')) {
        echo '<div class="error"><p><strong>OwnOmail Error:</strong> The PHP extension <code>mbstring</code> is missing. This plugin requires mbstring to function properly.</p></div>';
    }
}
add_action('admin_notices', 'ownomail_mbstring_admin_notice');

// Load functionalities if mbstring is available
if (extension_loaded('mbstring')) {
    require_once plugin_dir_path(__FILE__) . 'includes/ownomail-email-functions.php';
    
    // Load admin settings if in admin area
    if (is_admin()) {
        require_once plugin_dir_path(__FILE__) . 'includes/ownomail-admin-settings.php';
    }
}
