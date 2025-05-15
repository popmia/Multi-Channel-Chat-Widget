<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

function mc_chat_cleanup_options() {
    $options = [
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
    ];

    $options = array_map('sanitize_key', $options);

    if (is_multisite()) {
        global $wpdb;
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
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
}

mc_chat_cleanup_options();
