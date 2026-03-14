<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!isset($_POST['school_id_card_maker_settings_nonce']) || !wp_verify_nonce($_POST['school_id_card_maker_settings_nonce'], 'save_settings')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    update_option('school_id_card_default_school_name', sanitize_text_field($_POST['default_school_name']));
    update_option('school_id_card_default_school_logo', esc_url_raw($_POST['default_school_logo']));
    update_option('school_id_card_default_school_address', sanitize_textarea_field($_POST['default_school_address']));
    update_option('school_id_card_default_school_contact', sanitize_text_field($_POST['default_school_contact']));
    update_option('school_id_card_default_school_email', sanitize_email($_POST['default_school_email']));
    update_option('school_id_card_default_school_website', esc_url_raw($_POST['default_school_website']));

    echo '<div class="saas-notice saas-notice-success"><p>Settings saved successfully.</p></div>';
}

$default_school_name = get_option('school_id_card_default_school_name', '');
$default_school_logo = get_option('school_id_card_default_school_logo', '');
$default_school_address = get_option('school_id_card_default_school_address', '');
$default_school_contact = get_option('school_id_card_default_school_contact', '');
$default_school_email = get_option('school_id_card_default_school_email', '');
$default_school_website = get_option('school_id_card_default_school_website', '');
?>

<div class="wrap saas-wrap">
    <h1>Settings</h1>

    <div class="saas-card" style="max-width: 600px;">
        <form method="post" action="">
            <?php wp_nonce_field('save_settings', 'school_id_card_maker_settings_nonce'); ?>

            <h2>General Information</h2>
            <div class="saas-form-group">
                <label for="default_school_name">Default School Name</label>
                <input name="default_school_name" type="text" id="default_school_name" value="<?php echo esc_attr($default_school_name); ?>" class="saas-input">
            </div>

            <div class="saas-form-group">
                <label for="default_school_logo">Default School Logo URL</label>
                <input name="default_school_logo" type="url" id="default_school_logo" value="<?php echo esc_url($default_school_logo); ?>" class="saas-input" placeholder="https://example.com/logo.png">
            </div>

            <div style="border-top: 1px solid var(--saas-border); margin: 24px 0;"></div>

            <h2>Contact Information</h2>
            <div class="saas-form-group">
                <label for="default_school_address">Default School Address</label>
                <textarea name="default_school_address" id="default_school_address" class="saas-textarea" rows="3"><?php echo esc_textarea($default_school_address); ?></textarea>
            </div>

            <div class="saas-grid-2">
                <div class="saas-form-group">
                    <label for="default_school_contact">Default Contact Number</label>
                    <input name="default_school_contact" type="text" id="default_school_contact" value="<?php echo esc_attr($default_school_contact); ?>" class="saas-input">
                </div>
                <div class="saas-form-group">
                    <label for="default_school_email">Default Email</label>
                    <input name="default_school_email" type="email" id="default_school_email" value="<?php echo esc_attr($default_school_email); ?>" class="saas-input">
                </div>
            </div>

            <div class="saas-form-group">
                <label for="default_school_website">Default Website</label>
                <input name="default_school_website" type="url" id="default_school_website" value="<?php echo esc_attr($default_school_website); ?>" class="saas-input" placeholder="https://...">
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" name="save_settings" class="saas-btn saas-btn-primary" style="padding: 10px 24px; font-size: 15px;">Save Settings</button>
            </div>
        </form>
    </div>
</div>
