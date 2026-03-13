<div class="id-card horizontal template-87" style="background-color: #f2e9e4;">
    <div class="header" style="background: #9a8c98; color: #fff; text-align: left; padding-left: 15px;">
                <?php if (!empty($student->school_logo)): ?><img src="<?php echo esc_url($student->school_logo); ?>" class="logo" alt="Logo"><?php endif; ?>
                <h2 class="school-name" style="color: #fff;"><?php echo esc_html(!empty($student->school_name) ? $student->school_name : get_option("school_id_card_default_school_name", "Default School")); ?></h2>
            </div>
    <div class="body">
        <div class="photo-container" style="top: 15px; right: 15px; left: auto; border-radius: 8px; border: 2px solid #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <?php if (!empty($student->student_photo)): ?>
                <img src="<?php echo esc_url($student->student_photo); ?>" class="photo" alt="Photo">
            <?php else: ?>
                <div class="photo-placeholder" style="line-height: 105px;">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details" style="top: 15px; left: 15px; right: 115px;">
            <h3 class="name" style="color: #9a8c98; border-color: #9a8c98;"><?php echo esc_html($student->student_name); ?></h3>
            <table>
                <tr><td>Class:</td><td><?php echo esc_html($student->class . ' ' . $student->section); ?></td></tr>
                <tr><td>Roll No:</td><td><?php echo esc_html($student->roll_no); ?></td></tr>
                <tr><td>DOB:</td><td><?php echo esc_html($student->dob); ?></td></tr>
                <tr><td>Blood Group:</td><td><?php echo esc_html($student->blood_group); ?></td></tr>
            </table>
        </div>
    </div>
    <div class="footer" style="background-color: #22223b; color: #ffffff;">
        <p class="school-address" style="color: #ffffff;"><?php echo nl2br(esc_html(get_option('school_id_card_default_school_address', '123 Default St.'))); ?></p>
        <p class="school-contact" style="color: rgba(255,255,255,0.7);"><?php echo esc_html(get_option('school_id_card_default_school_contact', '123-456-7890')); ?> | <?php echo esc_html(get_option('school_id_card_default_school_email', 'info@school.com')); ?></p>
    </div>
</div>