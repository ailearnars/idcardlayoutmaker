<div class="id-card vertical template-65" style="background-color: #ffffff;">
    <div class="header" style="background: #3F37C9; color: #ffffff; border-bottom: 5px solid #3F37C9; padding-top: 20px;">
                <h2 class="school-name" style="color: #ffffff; font-size: 15px;"><?php echo esc_html(!empty($student->school_name) ? $student->school_name : get_option("school_id_card_default_school_name", "Default School")); ?></h2>
            </div>
    <div class="body" style="background-color: #ffffff;">
        <div class="photo-container" style="border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.2); margin-top: -15px; z-index: 10; position: relative;">
            <?php if (!empty($student->student_photo)): ?>
                <img src="<?php echo esc_url($student->student_photo); ?>" class="photo" alt="Photo" style="object-fit: cover;">
            <?php else: ?>
                <div class="photo-placeholder">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details">
            <h3 class="name" style="color: #3F37C9; font-size: 16px; margin-bottom: 12px;"><?php echo esc_html($student->student_name); ?></h3>
            <table style="width: 80%;">
                <tr><td>Class:</td><td><?php echo esc_html($student->class . ' ' . $student->section); ?></td></tr>
                <tr><td>Roll No:</td><td><?php echo esc_html($student->roll_no); ?></td></tr>
                <tr><td>DOB:</td><td><?php echo esc_html($student->dob); ?></td></tr>
                <tr><td>Blood:</td><td><?php echo esc_html($student->blood_group); ?></td></tr>
            </table>
        </div>
    </div>
    <div class="footer" style="background-color: #3F37C9; color: #ffffff;">
        <p class="school-address" style="color: #ffffff;"><?php echo nl2br(esc_html(!empty($student->school_address) ? $student->school_address : get_option("school_id_card_default_school_address", "123 Default St."))); ?></p>
        <p class="school-contact" style="color: rgba(255,255,255,0.7);"><?php echo esc_html(!empty($student->school_contact) ? $student->school_contact : get_option("school_id_card_default_school_contact", "123-456-7890")); ?> | <?php echo esc_html(!empty($student->school_email) ? $student->school_email : get_option("school_id_card_default_school_email", "info@school.com")); ?></p>
    </div>
</div>