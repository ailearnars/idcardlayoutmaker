<div class="id-card vertical template-74" style="background-color: #ffffff;">
    <div class="header" style="border: none; background: #fff;">
                <h2 class="school-name" style="color: #264653; font-weight: normal; font-size: 15px;"><?php echo esc_html(!empty($student->school_name) ? $student->school_name : get_option("school_id_card_default_school_name", "Default School")); ?></h2>
            </div>
    <div class="body" style="background-color: #ffffff;">
        <div class="photo-container" style="border: none; box-shadow: none;">
            <?php if (!empty($student->student_photo)): ?>
                <img src="<?php echo esc_url($student->student_photo); ?>" class="photo" alt="Photo" style="object-fit: cover;">
            <?php else: ?>
                <div class="photo-placeholder">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details">
            <h3 class="name" style="color: #F4A261; font-size: 15px; margin-bottom: 6px;"><?php echo esc_html($student->student_name); ?></h3>
            <table style="width: 85%;">
                <tr><td>Class:</td><td><?php echo esc_html($student->class . ' ' . $student->section); ?></td></tr>
                <tr><td>Roll No:</td><td><?php echo esc_html($student->roll_no); ?></td></tr>
                <tr><td>DOB:</td><td><?php echo esc_html($student->dob); ?></td></tr>
                <tr><td>Blood:</td><td><?php echo esc_html($student->blood_group); ?></td></tr>
                <?php if(!empty($student->phone)): ?><tr><td>Phone:</td><td><?php echo esc_html($student->phone); ?></td></tr><?php endif; ?>
            </table>
        </div>
    </div>
    <div class="footer" style="background-color: #264653; color: #ffffff;">
        <p class="school-address" style="color: #ffffff;"><?php echo nl2br(esc_html(!empty($student->school_address) ? $student->school_address : get_option("school_id_card_default_school_address", "123 Default St."))); ?></p>
        <p class="school-contact" style="color: rgba(255,255,255,0.7);"><?php
            $c_info = [];
            $sc = !empty($student->school_contact) ? $student->school_contact : get_option("school_id_card_default_school_contact", "123-456-7890");
            if($sc) $c_info[] = $sc;

            $se = !empty($student->school_email) ? $student->school_email : get_option("school_id_card_default_school_email", "info@school.com");
            if($se) $c_info[] = $se;

            $sw = !empty($student->school_website) ? $student->school_website : get_option("school_id_card_default_school_website", "");
            if($sw) $c_info[] = $sw;

            echo esc_html(implode(" | ", $c_info));
        ?></p>
    </div>
</div>