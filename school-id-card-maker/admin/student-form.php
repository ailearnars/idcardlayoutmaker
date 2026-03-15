<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student = null;
if ($id > 0) {
    $student = school_id_card_maker_get_student($id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student_form'])) {
    if (!isset($_POST['school_id_card_maker_nonce']) || !wp_verify_nonce($_POST['school_id_card_maker_nonce'], 'save_student_data')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $photo_url = esc_url_raw($_POST['student_photo']);
    if (!empty($_FILES['student_photo_upload']['name'])) {
        $uploaded_url = school_id_card_maker_handle_image_upload($_FILES['student_photo_upload']);
        if ($uploaded_url) {
            $photo_url = $uploaded_url;
        }
    }

    $data = array(
        'school_id'     => intval($_POST['school_id']),
        'student_name'  => sanitize_text_field($_POST['student_name']),
        'student_photo' => $photo_url,
        'admission_no'  => sanitize_text_field($_POST['admission_no']),
        'class'         => sanitize_text_field($_POST['class']),
        'section'       => sanitize_text_field($_POST['section']),
        'roll_no'       => sanitize_text_field($_POST['roll_no']),
        'dob'           => empty($_POST['dob']) ? null : sanitize_text_field($_POST['dob']),
        'blood_group'   => sanitize_text_field($_POST['blood_group']),
        'phone'         => sanitize_text_field($_POST['phone']),
        'address'       => sanitize_textarea_field($_POST['address']),
        'father_name'   => sanitize_text_field($_POST['father_name']),
        'mother_name'   => sanitize_text_field($_POST['mother_name']),
        'school_name'   => '', // Deprecated override
        'school_logo'   => '', // Deprecated override
    );

    if ($id > 0) {
        school_id_card_maker_update_student($id, $data);
        echo '<div class="saas-notice saas-notice-success"><p>Student updated successfully.</p></div>';
        $student = school_id_card_maker_get_student($id);
    } else {
        $id = school_id_card_maker_add_student($data);
        echo '<div class="saas-notice saas-notice-success"><p>Student added successfully.</p></div>';
        $student = school_id_card_maker_get_student($id);
    }
}
?>

<div class="wrap saas-wrap">
    <h1>
        <?php echo $id > 0 ? 'Edit Student' : 'Add New Student'; ?>
        <a href="?page=school-id-card-maker" class="saas-btn saas-btn-secondary">Back to List</a>
    </h1>

    <div class="saas-card">
        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field('save_student_data', 'school_id_card_maker_nonce'); ?>

            <div class="saas-grid-2">
                <!-- Personal Info -->
                <div>
                    <h2>Personal Information</h2>
                    <div class="saas-form-group">
                        <label for="student_name">Student Name *</label>
                        <input name="student_name" type="text" id="student_name" value="<?php echo esc_attr($student->student_name ?? ''); ?>" class="saas-input" required>
                    </div>

                    <div class="saas-form-group">
                        <label for="student_photo_upload">Photo (Upload)</label>
                        <input type="file" name="student_photo_upload" id="student_photo_upload" accept="image/*" class="saas-input" style="padding: 8px;">
                        <p class="description" style="margin-top: 4px; font-size: 12px; color: var(--saas-text-muted);">Upload a photo. It will be converted to WebP automatically.</p>
                        <?php if (!empty($student->student_photo)): ?>
                            <div style="margin-top: 10px;">
                                <img src="<?php echo esc_url($student->student_photo); ?>" alt="Current Photo" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid var(--saas-border);">
                            </div>
                        <?php endif; ?>
                        <input type="hidden" name="student_photo" value="<?php echo esc_attr($student->student_photo ?? ''); ?>">
                    </div>

                    <div class="saas-form-group">
                        <label for="dob">Date of Birth</label>
                        <input name="dob" type="date" id="dob" value="<?php echo esc_attr($student->dob ?? ''); ?>" class="saas-input">
                    </div>

                    <div class="saas-form-group">
                        <label for="blood_group">Blood Group</label>
                        <input name="blood_group" type="text" id="blood_group" value="<?php echo esc_attr($student->blood_group ?? ''); ?>" class="saas-input" placeholder="e.g. O+">
                    </div>
                </div>

                <!-- Academic Info -->
                <div>
                    <h2>Academic Information</h2>
                    <div class="saas-form-group">
                        <label for="admission_no">Admission Number *</label>
                        <input name="admission_no" type="text" id="admission_no" value="<?php echo esc_attr($student->admission_no ?? ''); ?>" class="saas-input" required>
                    </div>

                    <div class="saas-grid-2" style="gap: 16px;">
                        <div class="saas-form-group">
                            <label for="class">Class *</label>
                            <input name="class" type="text" id="class" value="<?php echo esc_attr($student->class ?? ''); ?>" class="saas-input" required>
                        </div>
                        <div class="saas-form-group">
                            <label for="section">Section</label>
                            <input name="section" type="text" id="section" value="<?php echo esc_attr($student->section ?? ''); ?>" class="saas-input">
                        </div>
                    </div>

                    <div class="saas-form-group">
                        <label for="roll_no">Roll Number</label>
                        <input name="roll_no" type="text" id="roll_no" value="<?php echo esc_attr($student->roll_no ?? ''); ?>" class="saas-input">
                    </div>

                    <div class="saas-form-group">
                        <label for="school_id">Assign to School</label>
                        <select name="school_id" id="school_id" class="saas-select">
                            <option value="0">-- Default Settings / Unassigned --</option>
                            <?php
                            $schools = school_id_card_maker_get_all_schools();
                            $global_school_id = get_user_meta(get_current_user_id(), 'school_id_card_active_school', true);
                            $current_school_id = isset($student->school_id) ? $student->school_id : ($global_school_id ? $global_school_id : 0);
                            foreach ($schools as $s) {
                                echo '<option value="' . esc_attr($s->id) . '" ' . selected($current_school_id, $s->id, false) . '>' . esc_html($s->school_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--saas-border); margin: 24px 0;"></div>

            <!-- Contact & Family Info -->
            <h2>Contact & Family Information</h2>
            <div class="saas-grid-2">
                <div class="saas-form-group">
                    <label for="father_name">Father's Name</label>
                    <input name="father_name" type="text" id="father_name" value="<?php echo esc_attr($student->father_name ?? ''); ?>" class="saas-input">
                </div>
                <div class="saas-form-group">
                    <label for="mother_name">Mother's Name</label>
                    <input name="mother_name" type="text" id="mother_name" value="<?php echo esc_attr($student->mother_name ?? ''); ?>" class="saas-input">
                </div>
            </div>

            <div class="saas-form-group">
                <label for="phone">Phone Number</label>
                <input name="phone" type="text" id="phone" value="<?php echo esc_attr($student->phone ?? ''); ?>" class="saas-input" style="max-width: 400px;">
            </div>

            <div class="saas-form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" class="saas-textarea" rows="3"><?php echo esc_textarea($student->address ?? ''); ?></textarea>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" name="submit_student_form" class="saas-btn saas-btn-primary" style="padding: 10px 24px; font-size: 15px;">Save Student</button>
            </div>
        </form>
    </div>
</div>
