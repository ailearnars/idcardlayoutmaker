<?php
$horizontal_dir = __DIR__ . '/templates/horizontal/';
$vertical_dir = __DIR__ . '/templates/vertical/';

// Ensure directories exist
if (!is_dir($horizontal_dir)) mkdir($horizontal_dir, 0755, true);
if (!is_dir($vertical_dir)) mkdir($vertical_dir, 0755, true);

// Colors array to make them look slightly different
$colors = ['#ff9999', '#99ff99', '#9999ff', '#ffff99', '#ff99ff', '#99ffff', '#f4f4f4', '#e0e0e0', '#dcdcdc', '#f0f8ff'];

// Generate 50 Horizontal Templates
for ($i = 1; $i <= 50; $i++) {
    $color = $colors[$i % count($colors)];
    $html = <<<EOD
<div class="id-card horizontal template-$i" style="background-color: $color;">
    <div class="header">
        <?php if (!empty(\$student->school_logo)): ?>
            <img src="<?php echo esc_url(\$student->school_logo); ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <h2 class="school-name"><?php echo esc_html(!empty(\$student->school_name) ? \$student->school_name : get_option('school_id_card_default_school_name', 'Default School')); ?></h2>
    </div>
    <div class="body">
        <div class="photo-container">
            <?php if (!empty(\$student->student_photo)): ?>
                <img src="<?php echo esc_url(\$student->student_photo); ?>" class="photo" alt="Photo">
            <?php else: ?>
                <div class="photo-placeholder">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details">
            <h3 class="name"><?php echo esc_html(\$student->student_name); ?></h3>
            <p><strong>Class:</strong> <?php echo esc_html(\$student->class . ' ' . \$student->section); ?></p>
            <p><strong>Roll No:</strong> <?php echo esc_html(\$student->roll_no); ?></p>
            <p><strong>DOB:</strong> <?php echo esc_html(\$student->dob); ?></p>
            <p><strong>Blood Group:</strong> <?php echo esc_html(\$student->blood_group); ?></p>
        </div>
    </div>
    <div class="footer">
        <p class="school-address"><?php echo nl2br(esc_html(get_option('school_id_card_default_school_address', '123 Default St.'))); ?></p>
        <p class="school-contact"><?php echo esc_html(get_option('school_id_card_default_school_contact', '123-456-7890')); ?> | <?php echo esc_html(get_option('school_id_card_default_school_email', 'info@school.com')); ?></p>
    </div>
</div>
EOD;
    file_put_contents($horizontal_dir . "template-$i.php", $html);
}

// Generate 50 Vertical Templates
for ($i = 1; $i <= 50; $i++) {
    $color = $colors[$i % count($colors)];
    $html = <<<EOD
<div class="id-card vertical template-$i" style="background-color: $color;">
    <div class="header">
        <?php if (!empty(\$student->school_logo)): ?>
            <img src="<?php echo esc_url(\$student->school_logo); ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <h2 class="school-name"><?php echo esc_html(!empty(\$student->school_name) ? \$student->school_name : get_option('school_id_card_default_school_name', 'Default School')); ?></h2>
    </div>
    <div class="body">
        <div class="photo-container">
            <?php if (!empty(\$student->student_photo)): ?>
                <img src="<?php echo esc_url(\$student->student_photo); ?>" class="photo" alt="Photo">
            <?php else: ?>
                <div class="photo-placeholder">Photo</div>
            <?php endif; ?>
        </div>
        <div class="details">
            <h3 class="name"><?php echo esc_html(\$student->student_name); ?></h3>
            <p><strong>Class:</strong> <?php echo esc_html(\$student->class . ' ' . \$student->section); ?></p>
            <p><strong>Roll No:</strong> <?php echo esc_html(\$student->roll_no); ?></p>
            <p><strong>DOB:</strong> <?php echo esc_html(\$student->dob); ?></p>
            <p><strong>Blood:</strong> <?php echo esc_html(\$student->blood_group); ?></p>
        </div>
    </div>
    <div class="footer">
        <p class="school-address"><?php echo nl2br(esc_html(get_option('school_id_card_default_school_address', '123 Default St.'))); ?></p>
        <p class="school-contact"><?php echo esc_html(get_option('school_id_card_default_school_contact', '123-456-7890')); ?> | <?php echo esc_html(get_option('school_id_card_default_school_email', 'info@school.com')); ?></p>
    </div>
</div>
EOD;
    file_put_contents($vertical_dir . "template-$i.php", $html);
}

echo "Templates generated successfully.\n";
