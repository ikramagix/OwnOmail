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
