<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// List of options to delete
$ownomail_options = [
    'ownomail_sender_email',
    'ownomail_sender_name',
];

// Delete each option
foreach ($ownomail_options as $option) {
    delete_option($option);
    delete_site_option($option);
}

// If created custom database tables are created
/*
global $wpdb;
$table_name = $wpdb->prefix . "ownomail_logs"; // Replace with actual table name
$wpdb->query("DROP TABLE IF EXISTS $table_name");
*/

// If plugin stores user metadata (not relevant here, but for reference)
/*
delete_metadata('user', 0, 'ownomail_custom_meta_key', '', true);
*/

// Done: Plugin is fully uninstalled.