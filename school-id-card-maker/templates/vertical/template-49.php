<div class="id-card vertical template-49" style="background-color: #f0f8ff;">
    <div class="header">
        <?php if (!empty($student->school_logo)): ?>
            <img src="<?php echo esc_url($student->school_logo); ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <h2 class="school-name"><?php echo esc_html(!empty($student->school_name) ? $student->school_name : get_option('school_id_card_default_school_name', 'Default School')); ?></h2>
    </div>
    <div class="body">
        <div class="photo-container">
            <?php if (!empty($student->student_photo)): ?>
                <img src="<?php echo esc_url($student->student_photo); ?>" class="photo" alt="Photo">
            <?php else: ?>
                <div class="photo-placeholder">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details">
            <h3 class="name"><?php echo esc_html($student->student_name); ?></h3>
            <p><strong>Class:</strong> <?php echo esc_html($student->class . ' ' . $student->section); ?></p>
            <p><strong>Roll No:</strong> <?php echo esc_html($student->roll_no); ?></p>
            <p><strong>DOB:</strong> <?php echo esc_html($student->dob); ?></p>
            <p><strong>Blood:</strong> <?php echo esc_html($student->blood_group); ?></p>
        </div>
    </div>
    <div class="footer">
        <p class="school-address"><?php echo nl2br(esc_html(get_option('school_id_card_default_school_address', '123 Default St.'))); ?></p>
        <p class="school-contact"><?php echo esc_html(get_option('school_id_card_default_school_contact', '123-456-7890')); ?> | <?php echo esc_html(get_option('school_id_card_default_school_email', 'info@school.com')); ?></p>
    </div>
</div>