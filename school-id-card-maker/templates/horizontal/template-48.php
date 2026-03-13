<div class="id-card horizontal template-48" style="background-color: #ffffff;">
    <div class="header" style="border-bottom: 1px solid #2C6E49; padding-bottom: 5px; background: #fff;">
                <h2 class="school-name" style="color: #333; letter-spacing: 2px;"><?php echo esc_html(!empty($student->school_name) ? $student->school_name : get_option("school_id_card_default_school_name", "Default School")); ?></h2>
            </div>
    <div class="body" style="background-color: #ffffff;">
        <div class="photo-container" style="top: 15px; left: 15px; border-radius: 50%; width: 85px; height: 85px; border: 3px solid #2C6E49;">
            <?php if (!empty($student->student_photo)): ?>
                <img src="<?php echo esc_url($student->student_photo); ?>" class="photo" alt="Photo" style="object-fit: cover;">
            <?php else: ?>
                <div class="photo-placeholder" style="line-height: 105px;">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details" style="top: 15px; left: 115px;">
            <h3 class="name" style="color: #2C6E49; border-color: #2C6E49;"><?php echo esc_html($student->student_name); ?></h3>
            <table>
                <tr><td>Class:</td><td><?php echo esc_html($student->class . ' ' . $student->section); ?></td></tr>
                <tr><td>Roll No:</td><td><?php echo esc_html($student->roll_no); ?></td></tr>
                <tr><td>DOB:</td><td><?php echo esc_html($student->dob); ?></td></tr>
                <tr><td>Blood Group:</td><td><?php echo esc_html($student->blood_group); ?></td></tr>
            </table>
        </div>
    </div>
    <div class="footer" style="background-color: #143601; color: #ffffff;">
        <p class="school-address" style="color: #ffffff;"><?php echo nl2br(esc_html(!empty($student->school_address) ? $student->school_address : get_option("school_id_card_default_school_address", "123 Default St."))); ?></p>
        <p class="school-contact" style="color: rgba(255,255,255,0.7);"><?php echo esc_html(!empty($student->school_contact) ? $student->school_contact : get_option("school_id_card_default_school_contact", "123-456-7890")); ?> | <?php echo esc_html(!empty($student->school_email) ? $student->school_email : get_option("school_id_card_default_school_email", "info@school.com")); ?></p>
    </div>
</div>