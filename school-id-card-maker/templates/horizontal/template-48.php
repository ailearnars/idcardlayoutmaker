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
        <div class="details" style="top: 10px; left: 115px; right: 5px;">
            <h3 class="name" style="color: #2C6E49; border-color: #2C6E49; margin-bottom: 4px;"><?php echo esc_html($student->student_name); ?></h3>
            <table>
                <?php if(!empty($student->class)): ?><tr><td>Class:</td><td><?php echo esc_html($student->class . ' ' . $student->section); ?></td></tr><?php endif; ?>
                <?php if(!empty($student->roll_no)): ?><tr><td>Roll No:</td><td><?php echo esc_html($student->roll_no); ?></td></tr><?php endif; ?>
                <?php if(!empty($student->dob)): ?><tr><td>DOB:</td><td><?php echo esc_html($student->dob); ?></td></tr><?php endif; ?>
                <?php if(!empty($student->blood_group)): ?><tr><td>Blood Grp:</td><td><?php echo esc_html($student->blood_group); ?></td></tr><?php endif; ?>
                <?php if(!empty($student->father_name)): ?><tr><td>Father:</td><td><?php echo esc_html($student->father_name); ?></td></tr><?php endif; ?>
                <?php if(!empty($student->mother_name)): ?><tr><td>Mother:</td><td><?php echo esc_html($student->mother_name); ?></td></tr><?php endif; ?>
                <?php if(!empty($student->phone)): ?><tr><td>Phone:</td><td><?php echo esc_html($student->phone); ?></td></tr><?php endif; ?>
            </table>
            <?php if(!empty($student->address)): ?><p style="font-size: 9px; line-height: 1.1; color: #333; margin: 3px 0 0 0; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"><strong>Address:</strong> <?php echo esc_html($student->address); ?></p><?php endif; ?>
        </div>
    </div>
    <div class="footer" style="background-color: #143601; color: #ffffff;">
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

    <?php $sig_url = !empty($student->principal_signature) ? $student->principal_signature : get_option("school_id_card_default_principal_signature", "");
    if(!empty($sig_url)): ?>
        <img src="<?php echo esc_url($sig_url); ?>" alt="Signature" style="height: 30px; position: absolute; bottom: 40px; right: 10px; z-index: 10;">
    <?php endif; ?>
</div>
