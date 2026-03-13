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

    $data = array(
        'student_name'  => sanitize_text_field($_POST['student_name']),
        'student_photo' => esc_url_raw($_POST['student_photo']),
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
        'school_name'   => sanitize_text_field($_POST['school_name']),
        'school_logo'   => esc_url_raw($_POST['school_logo']),
    );

    if ($id > 0) {
        school_id_card_maker_update_student($id, $data);
        echo '<div class="notice notice-success is-dismissible"><p>Student updated successfully.</p></div>';
        $student = school_id_card_maker_get_student($id);
    } else {
        $id = school_id_card_maker_add_student($data);
        echo '<div class="notice notice-success is-dismissible"><p>Student added successfully.</p></div>';
        $student = school_id_card_maker_get_student($id);
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $id > 0 ? 'Edit Student' : 'Add New Student'; ?></h1>
    <a href="?page=school-id-card-maker" class="page-title-action">Back to List</a>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('save_student_data', 'school_id_card_maker_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="student_name">Student Name *</label></th>
                <td><input name="student_name" type="text" id="student_name" value="<?php echo esc_attr($student->student_name ?? ''); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="student_photo">Photo URL</label></th>
                <td>
                    <input name="student_photo" type="url" id="student_photo" value="<?php echo esc_url($student->student_photo ?? ''); ?>" class="regular-text">
                    <p class="description">URL to the student's photo. (Media library integration coming soon)</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="admission_no">Admission Number *</label></th>
                <td><input name="admission_no" type="text" id="admission_no" value="<?php echo esc_attr($student->admission_no ?? ''); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="class">Class *</label></th>
                <td><input name="class" type="text" id="class" value="<?php echo esc_attr($student->class ?? ''); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="section">Section</label></th>
                <td><input name="section" type="text" id="section" value="<?php echo esc_attr($student->section ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="roll_no">Roll Number</label></th>
                <td><input name="roll_no" type="text" id="roll_no" value="<?php echo esc_attr($student->roll_no ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="dob">Date of Birth</label></th>
                <td><input name="dob" type="date" id="dob" value="<?php echo esc_attr($student->dob ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="blood_group">Blood Group</label></th>
                <td><input name="blood_group" type="text" id="blood_group" value="<?php echo esc_attr($student->blood_group ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="father_name">Father's Name</label></th>
                <td><input name="father_name" type="text" id="father_name" value="<?php echo esc_attr($student->father_name ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="mother_name">Mother's Name</label></th>
                <td><input name="mother_name" type="text" id="mother_name" value="<?php echo esc_attr($student->mother_name ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="phone">Phone Number</label></th>
                <td><input name="phone" type="text" id="phone" value="<?php echo esc_attr($student->phone ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="address">Address</label></th>
                <td><textarea name="address" id="address" class="large-text" rows="3"><?php echo esc_textarea($student->address ?? ''); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="school_name">School Name</label></th>
                <td><input name="school_name" type="text" id="school_name" value="<?php echo esc_attr($student->school_name ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="school_logo">School Logo URL</label></th>
                <td><input name="school_logo" type="url" id="school_logo" value="<?php echo esc_url($student->school_logo ?? ''); ?>" class="regular-text"></td>
            </tr>
        </table>
        <?php submit_button('Save Student', 'primary', 'submit_student_form'); ?>
    </form>
</div>
