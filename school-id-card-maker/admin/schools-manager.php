<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$school_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle Delete
if ($action === 'delete' && $school_id > 0) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_school')) {
        wp_die('Security check failed');
    }
    school_id_card_maker_delete_school($school_id);
    echo '<div class="saas-notice saas-notice-success"><p>School deleted successfully.</p></div>';
    $action = 'list';
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_school'])) {
    if (!isset($_POST['school_nonce']) || !wp_verify_nonce($_POST['school_nonce'], 'save_school_action')) {
        wp_die('Security check failed');
    }

    $logo_url = esc_url_raw($_POST['school_logo']);
    if (!empty($_FILES['school_logo_upload']['name'])) {
        $uploaded_url = school_id_card_maker_handle_image_upload($_FILES['school_logo_upload']);
        if ($uploaded_url) {
            $logo_url = $uploaded_url;
        }
    }

    $data = array(
        'school_name'    => sanitize_text_field($_POST['school_name']),
        'school_logo'    => $logo_url,
        'school_address' => sanitize_textarea_field($_POST['school_address']),
        'school_contact' => sanitize_text_field($_POST['school_contact']),
        'school_email'   => sanitize_email($_POST['school_email']),
        'school_website' => esc_url_raw($_POST['school_website']),
    );

    if ($school_id > 0) {
        school_id_card_maker_update_school($school_id, $data);
        echo '<div class="saas-notice saas-notice-success"><p>School updated successfully.</p></div>';
    } else {
        $school_id = school_id_card_maker_add_school($data);
        echo '<div class="saas-notice saas-notice-success"><p>School added successfully.</p></div>';
    }
    $action = 'list';
}

if ($action === 'list') {
    $schools = school_id_card_maker_get_all_schools();
?>
    <div class="wrap saas-wrap">
        <h1>Manage Schools
            <a href="?page=school-id-card-maker-schools&action=add" class="saas-btn saas-btn-primary">Add New School</a>
        </h1>

        <div class="saas-table-container">
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>School Name</th>
                        <th>Contact Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schools)) : ?>
                        <?php foreach ($schools as $s) : ?>
                            <tr>
                                <td><?php echo esc_html($s->id); ?></td>
                                <td><strong><a href="?page=school-id-card-maker-schools&action=edit&id=<?php echo esc_attr($s->id); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html($s->school_name); ?></a></strong></td>
                                <td><?php echo esc_html($s->school_email); ?></td>
                                <td>
                                    <div class="saas-actions">
                                        <a href="?page=school-id-card-maker-schools&action=edit&id=<?php echo esc_attr($s->id); ?>" class="saas-action-edit">Edit</a>
                                        <a href="<?php echo wp_nonce_url("?page=school-id-card-maker-schools&action=delete&id={$s->id}", 'delete_school'); ?>" class="saas-action-delete" onclick="return confirm('Are you sure you want to delete this school?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 32px; color: var(--saas-text-muted);">No schools found. Add a school to manage its data.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
} elseif ($action === 'add' || $action === 'edit') {
    $school = null;
    if ($school_id > 0) {
        $school = school_id_card_maker_get_school($school_id);
    }
?>
    <div class="wrap saas-wrap">
        <h1><?php echo $school_id > 0 ? 'Edit School' : 'Add New School'; ?>
            <a href="?page=school-id-card-maker-schools" class="saas-btn saas-btn-secondary">Back to Schools</a>
        </h1>

        <div class="saas-card" style="max-width: 600px;">
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('save_school_action', 'school_nonce'); ?>

                <div class="saas-form-group">
                    <label>School Name *</label>
                    <input type="text" name="school_name" value="<?php echo esc_attr($school->school_name ?? ''); ?>" class="saas-input" required>
                </div>

                <div class="saas-form-group">
                    <label>School Logo (Upload)</label>
                    <input type="file" name="school_logo_upload" accept="image/*" class="saas-input" style="padding: 8px;">
                    <p class="description" style="margin-top: 4px; font-size: 12px; color: var(--saas-text-muted);">Upload a logo. It will be automatically converted to WebP.</p>
                    <?php if (!empty($school->school_logo)): ?>
                        <div style="margin-top: 10px;">
                            <img src="<?php echo esc_url($school->school_logo); ?>" alt="Current Logo" style="max-height: 80px; max-width: 100%; border: 1px solid var(--saas-border); border-radius: 4px;">
                        </div>
                    <?php endif; ?>
                    <input type="hidden" name="school_logo" value="<?php echo esc_attr($school->school_logo ?? ''); ?>">
                </div>

                <div class="saas-form-group">
                    <label>School Address</label>
                    <textarea name="school_address" class="saas-textarea" rows="3"><?php echo esc_textarea($school->school_address ?? ''); ?></textarea>
                </div>

                <div class="saas-grid-2">
                    <div class="saas-form-group">
                        <label>Contact Number</label>
                        <input type="text" name="school_contact" value="<?php echo esc_attr($school->school_contact ?? ''); ?>" class="saas-input">
                    </div>
                    <div class="saas-form-group">
                        <label>Email Address</label>
                        <input type="email" name="school_email" value="<?php echo esc_attr($school->school_email ?? ''); ?>" class="saas-input">
                    </div>
                </div>

                <div class="saas-form-group">
                    <label>School Website URL</label>
                    <input type="url" name="school_website" value="<?php echo esc_attr($school->school_website ?? ''); ?>" class="saas-input" placeholder="https://...">
                </div>

                <div style="margin-top: 32px;">
                    <button type="submit" name="save_school" class="saas-btn saas-btn-primary" style="padding: 10px 24px; font-size: 15px;">Save School</button>
                </div>
            </form>
        </div>
    </div>
<?php
}
