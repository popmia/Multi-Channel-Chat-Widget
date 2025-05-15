<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$options = array_map('sanitize_key', [
    'mc_chat_whatsapp',
    'mc_chat_show_whatsapp',
    'mc_chat_greeting',
    'mc_chat_custom_message_text',
    'mc_chat_email_address',
    'mc_chat_show_gmail',
    'mc_chat_show_hotmail',
    'mc_chat_show_outlook',
    'mc_chat_label_reason_1',
    'mc_chat_label_reason_2',
    'mc_chat_label_reason_3',
    'mc_chat_label_reason_4',
    'mc_chat_show_facebook',
    'mc_chat_facebook_username'
]);

if (is_multisite()) {
    $sites = get_sites();
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        foreach ($options as $key) {
            delete_option($key);
        }
        restore_current_blog();
    }
} else {
    foreach ($options as $key) {
        delete_option($key);
    }
}
