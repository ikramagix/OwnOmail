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
