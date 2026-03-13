<div class="id-card vertical template-72" style="background-color: #ffffff;">
    <div class="header" style="background: #F4A261; color: #fff;">
                <?php $logo_url = !empty($student->school_logo) ? $student->school_logo : get_option("school_id_card_default_school_logo", ""); if(!empty($logo_url)): ?><img src="<?php echo esc_url($logo_url); ?>" class="logo" alt="Logo"><?php endif; ?>
                <h2 class="school-name" style="color: #fff;"><?php echo esc_html(!empty($student->school_name) ? $student->school_name : get_option("school_id_card_default_school_name", "Default School")); ?></h2>
            </div>
    <div class="body" style="background-color: #ffffff;">
        <div class="photo-container" style="border-radius: 8px; border: 3px solid #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 10px;">
            <?php if (!empty($student->student_photo)): ?>
                <img src="<?php echo esc_url($student->student_photo); ?>" class="photo" alt="Photo" style="object-fit: cover;">
            <?php else: ?>
                <div class="photo-placeholder">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details">
            <h3 class="name" style="color: #F4A261; font-size: 16px; margin-bottom: 12px;"><?php echo esc_html($student->student_name); ?></h3>
            <table style="width: 80%;">
                <tr><td>Class:</td><td><?php echo esc_html($student->class . ' ' . $student->section); ?></td></tr>
                <tr><td>Roll No:</td><td><?php echo esc_html($student->roll_no); ?></td></tr>
                <tr><td>DOB:</td><td><?php echo esc_html($student->dob); ?></td></tr>
                <tr><td>Blood:</td><td><?php echo esc_html($student->blood_group); ?></td></tr>
            </table>
        </div>
    </div>
    <div class="footer" style="background-color: #264653; color: #ffffff;">
        <p class="school-address" style="color: #ffffff;"><?php echo nl2br(esc_html(get_option('school_id_card_default_school_address', '123 Default St.'))); ?></p>
        <p class="school-contact" style="color: rgba(255,255,255,0.7);"><?php echo esc_html(get_option('school_id_card_default_school_contact', '123-456-7890')); ?> | <?php echo esc_html(get_option('school_id_card_default_school_email', 'info@school.com')); ?></p>
    </div>
</div>