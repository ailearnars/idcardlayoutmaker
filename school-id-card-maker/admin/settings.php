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

    echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
}

$default_school_name = get_option('school_id_card_default_school_name', '');
$default_school_logo = get_option('school_id_card_default_school_logo', '');
$default_school_address = get_option('school_id_card_default_school_address', '');
$default_school_contact = get_option('school_id_card_default_school_contact', '');
$default_school_email = get_option('school_id_card_default_school_email', '');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Settings</h1>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('save_settings', 'school_id_card_maker_settings_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="default_school_name">Default School Name</label></th>
                <td><input name="default_school_name" type="text" id="default_school_name" value="<?php echo esc_attr($default_school_name); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="default_school_logo">Default School Logo URL</label></th>
                <td><input name="default_school_logo" type="url" id="default_school_logo" value="<?php echo esc_url($default_school_logo); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="default_school_address">Default School Address</label></th>
                <td><textarea name="default_school_address" id="default_school_address" class="large-text" rows="3"><?php echo esc_textarea($default_school_address); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="default_school_contact">Default School Contact</label></th>
                <td><input name="default_school_contact" type="text" id="default_school_contact" value="<?php echo esc_attr($default_school_contact); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="default_school_email">Default School Email</label></th>
                <td><input name="default_school_email" type="email" id="default_school_email" value="<?php echo esc_attr($default_school_email); ?>" class="regular-text"></td>
            </tr>
        </table>

        <?php submit_button('Save Settings', 'primary', 'save_settings'); ?>
    </form>
</div>
