<?php
/**
 * Plugin Name:       Multi-Channel Chat Widget
 * Plugin URI:        https://cohostdr.com
 * Description:       Adds a floating multichannel chat widget with WhatsApp, email, and Facebook.
 * Version:           2.4.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Taylan Evrenler
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       multi-channel-chat-widget
 */

defined('ABSPATH') or exit;

// Load translations
function mc_chat_load_textdomain() {
    load_plugin_textdomain('multi-channel-chat-widget', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'mc_chat_load_textdomain');

// Admin assets
function mc_chat_admin_assets($hook) {
    if ($hook === 'settings_page_mc-chat-settings') {
        wp_enqueue_style('mc-chat-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin.css', [], '2.4.0');
        wp_enqueue_script('mc-chat-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin.js', [], '2.4.0', true);
        wp_localize_script('mc-chat-admin-script', 'mcChatAdmin', array(
            'fbRequired' => esc_html__('Please enter a Facebook username before enabling.', 'multi-channel-chat-widget'),
            'emailRequired' => esc_html__('Please enter an email address before enabling email channels.', 'multi-channel-chat-widget'),
        ));
    }
}
add_action('admin_enqueue_scripts', 'mc_chat_admin_assets');

// Widget activation check
function mc_chat_is_enabled() {
    return get_option('mc_chat_show_whatsapp') === '1'
        || get_option('mc_chat_show_gmail') === '1'
        || get_option('mc_chat_show_hotmail') === '1'
        || get_option('mc_chat_show_outlook') === '1'
        || (get_option('mc_chat_show_facebook') === '1' && get_option('mc_chat_facebook_username'));
}

// Add settings link
function mc_chat_plugin_action_links($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=mc-chat-settings')) . '">' . esc_html__('Settings', 'multi-channel-chat-widget') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mc_chat_plugin_action_links');

// Frontend assets
function mc_chat_enqueue_assets() {
    if (!is_admin() && mc_chat_is_enabled()) {
        wp_enqueue_style('mc-chat-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '2.4.0');
        wp_enqueue_script('mc-chat-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '2.4.0', true);

        $reasons = array_filter([
            get_option('mc_chat_label_reason_1', ''),
            get_option('mc_chat_label_reason_2', ''),
            get_option('mc_chat_label_reason_3', ''),
            get_option('mc_chat_label_reason_4', '')
        ], function ($label) {
            return trim($label) !== '';
        });

        wp_localize_script('mc-chat-script', 'mcChatData', array(
            'whatsapp_number' => get_option('mc_chat_whatsapp', ''),
            'greeting_text' => esc_html__('Need help? Tap to message us!', 'multi-channel-chat-widget'),
            'custom_message_text' => get_option('mc_chat_custom_message_text', ''),
            'show_whatsapp' => get_option('mc_chat_show_whatsapp', '0'),
            'show_gmail' => get_option('mc_chat_show_gmail', '0'),
            'show_hotmail' => get_option('mc_chat_show_hotmail', '0'),
            'show_outlook' => get_option('mc_chat_show_outlook', '0'),
            'show_facebook' => get_option('mc_chat_show_facebook', '0'),
            'facebook_username' => get_option('mc_chat_facebook_username', ''),
            'email_address' => trim(get_option('mc_chat_email_address', '')),
            'reasons' => array_values($reasons),
            'i18n' => array(
                'name_required' => esc_html__('Please enter both your name and phone number.', 'multi-channel-chat-widget'),
                'email_missing' => esc_html__('Email address is not configured. Please update plugin settings.', 'multi-channel-chat-widget'),
                'inquiry_subject' => esc_html__('Inquiry from', 'multi-channel-chat-widget'),
                'general_inquiry' => esc_html__('General Inquiry', 'multi-channel-chat-widget'),
                'continue' => esc_html__('Continue', 'multi-channel-chat-widget'),
                'back' => esc_html__('Back', 'multi-channel-chat-widget'),
                'whatsapp' => esc_html__('WhatsApp', 'multi-channel-chat-widget'),
                'gmail' => esc_html__('Gmail', 'multi-channel-chat-widget'),
                'hotmail' => esc_html__('Hotmail', 'multi-channel-chat-widget'),
                'outlook' => esc_html__('Outlook', 'multi-channel-chat-widget'),
                'facebook' => esc_html__('Facebook Messenger', 'multi-channel-chat-widget'),
            )
        ));
    }
}
add_action('wp_enqueue_scripts', 'mc_chat_enqueue_assets');

// Display widget container
function mc_chat_display_widget() {
    if (mc_chat_is_enabled()) {
        echo '<div id="mc-chat-widget"></div>';
    }
}
add_action('wp_footer', 'mc_chat_display_widget');

// Register settings
function mc_chat_register_settings() {
    $fields = [
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

    foreach ($fields as $key) {
        register_setting('mc_chat_settings_group', $key, ['sanitize_callback' => 'sanitize_text_field']);
    }
}
add_action('admin_init', 'mc_chat_register_settings');

// Admin menu
function mc_chat_add_admin_menu() {
    add_options_page(
        esc_html__('Multi-Channel Chat Settings', 'multi-channel-chat-widget'),
        esc_html__('Multi-Channel Chat', 'multi-channel-chat-widget'),
        'manage_options',
        'mc-chat-settings',
        'mc_chat_render_settings_page'
    );
}
add_action('admin_menu', 'mc_chat_add_admin_menu');

// Deactivation
register_deactivation_hook(__FILE__, 'mc_chat_deactivate_cleanup');
function mc_chat_deactivate_cleanup() {
    // Cleanup handled in uninstall.php
}

// Settings UI
function mc_chat_render_settings_page() {
    $fb_user = get_option('mc_chat_facebook_username');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Multi-Channel Chat Settings', 'multi-channel-chat-widget'); ?></h1>

        <?php if (!mc_chat_is_enabled()): ?>
            <div class="notice notice-warning">
                <p><strong><?php esc_html_e('Note:', 'multi-channel-chat-widget'); ?></strong> <?php esc_html_e('No channels are currently enabled. The chat widget will not appear.', 'multi-channel-chat-widget'); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php" id="mc-chat-settings-form">
            <?php settings_fields('mc_chat_settings_group'); ?>
            <table class="form-table">
                <tr><th><?php esc_html_e('Enable WhatsApp', 'multi-channel-chat-widget'); ?></th><td><input type="checkbox" name="mc_chat_show_whatsapp" value="1" <?php checked(1, get_option('mc_chat_show_whatsapp'), true); ?> /></td></tr>
                <tr><th><?php esc_html_e('WhatsApp Number', 'multi-channel-chat-widget'); ?></th><td><input type="text" name="mc_chat_whatsapp" value="<?php echo esc_attr(get_option('mc_chat_whatsapp', '')); ?>" /></td></tr>
                <tr><th><?php esc_html_e('Greeting Message', 'multi-channel-chat-widget'); ?></th><td><input type="text" name="mc_chat_greeting" value="<?php echo esc_attr(get_option('mc_chat_greeting')); ?>" /></td></tr>
                <tr><th><?php esc_html_e('Intro Message', 'multi-channel-chat-widget'); ?></th><td><input type="text" name="mc_chat_custom_message_text" value="<?php echo esc_attr(get_option('mc_chat_custom_message_text')); ?>" /></td></tr>
                <tr><th><?php esc_html_e('Email Address (Gmail/Hotmail/Outlook)', 'multi-channel-chat-widget'); ?></th>
                    <td>
                        <input type="email" name="mc_chat_email_address" value="<?php echo esc_attr(get_option('mc_chat_email_address', '')); ?>" />
                        <p class="email-warning"><?php esc_html_e('Please enter a valid email address when enabling email channels.', 'multi-channel-chat-widget'); ?></p>
                    </td>
                </tr>
                <tr><th><?php esc_html_e('Show Gmail (Desktop Only)', 'multi-channel-chat-widget'); ?></th><td><input type="checkbox" class="email-toggle" name="mc_chat_show_gmail" value="1" <?php checked(1, get_option('mc_chat_show_gmail'), true); ?> /></td></tr>
                <tr><th><?php esc_html_e('Show Hotmail (Desktop Only)', 'multi-channel-chat-widget'); ?></th><td><input type="checkbox" class="email-toggle" name="mc_chat_show_hotmail" value="1" <?php checked(1, get_option('mc_chat_show_hotmail'), true); ?> /></td></tr>
                <tr><th><?php esc_html_e('Show Outlook (Desktop Only)', 'multi-channel-chat-widget'); ?></th><td><input type="checkbox" class="email-toggle" name="mc_chat_show_outlook" value="1" <?php checked(1, get_option('mc_chat_show_outlook'), true); ?> /></td></tr>

                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <tr>
<?php /* translators: %d is the reason number shown on the chat widget */ ?>
<th><?php echo esc_html(sprintf(__('Reason %d', 'multi-channel-chat-widget'), $i)); ?></th>
                        <td><input type="text" name="mc_chat_label_reason_<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(get_option("mc_chat_label_reason_$i")); ?>" /></td>
                    </tr>
                <?php endfor; ?>

                <tr><th><?php esc_html_e('Enable Facebook Messenger', 'multi-channel-chat-widget'); ?></th>
                    <td>
                        <input type="checkbox" name="mc_chat_show_facebook" value="1" <?php checked(1, get_option('mc_chat_show_facebook'), true); ?> />
                        <p class="fb-warning"><?php esc_html_e('Enter Facebook username to enable.', 'multi-channel-chat-widget'); ?></p>
                    </td>
                </tr>
                <tr><th><?php esc_html_e('Facebook Username', 'multi-channel-chat-widget'); ?></th><td><input type="text" name="mc_chat_facebook_username" value="<?php echo esc_attr($fb_user); ?>" /></td></tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
