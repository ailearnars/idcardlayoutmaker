<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$horizontal_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/horizontal/*.php' );
$vertical_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/vertical/*.php' );

// Create a dummy student for live previews
$student = new stdClass();
$student->student_name = "John Doe";
$student->class = "10";
$student->section = "A";
$student->roll_no = "1234";
$student->dob = "2005-08-15";
$student->blood_group = "O+";
$student->address = "456 Mockingbird Ln\nSpringfield, USA";
$student->student_photo = ""; // Placeholder logic will take over
$student->school_logo = "";
$student->school_name = get_option('school_id_card_default_school_name', 'Springfield High');

?>

<style>
/* Base preview wrapper styles to contain scaled templates */
.preview-container {
    position: relative;
    overflow: hidden;
    margin: 0 auto 15px auto;
    border: 1px solid #ddd;
    background: #f9f9f9;
}

/* Horizontal Preview Box: standard size 350x220, scaled down to 50% */
.preview-container.preview-horizontal {
    width: 175px;
    height: 110px;
}

/* Vertical Preview Box: standard size 220x350, scaled down to 40% */
.preview-container.preview-vertical {
    width: 88px;
    height: 140px;
}

.preview-scaler {
    transform-origin: top left;
    position: absolute;
    top: 0;
    left: 0;
}

.preview-container.preview-horizontal .preview-scaler {
    transform: scale(0.5); /* 350px * 0.5 = 175px */
    width: 350px;
    height: 220px;
}

.preview-container.preview-vertical .preview-scaler {
    transform: scale(0.4); /* 220px * 0.4 = 88px */
    width: 220px;
    height: 350px;
}
</style>

<div class="wrap">
    <h1 class="wp-heading-inline">Template Library</h1>
    <hr class="wp-header-end">

    <h2>Horizontal Templates</h2>
    <div class="template-grid" style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php foreach ($horizontal_templates as $template) :
            $filename = basename($template, '.php');
        ?>
            <div class="template-card" style="border: 1px solid #ccc; padding: 15px; text-align: center; width: 220px; background: #fff;">
                <div class="preview-container preview-horizontal">
                    <div class="preview-scaler">
                        <?php
                        ob_start();
                        include $template;
                        echo ob_get_clean();
                        ?>
                    </div>
                </div>
                <h3><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></h3>
                <a href="?page=school-id-card-maker-generate&template=<?php echo urlencode($filename); ?>&orientation=horizontal" class="button button-primary">Use Template</a>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 style="margin-top: 40px;">Vertical Templates</h2>
    <div class="template-grid" style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php foreach ($vertical_templates as $template) :
            $filename = basename($template, '.php');
        ?>
            <div class="template-card" style="border: 1px solid #ccc; padding: 15px; text-align: center; width: 140px; background: #fff;">
                <div class="preview-container preview-vertical">
                    <div class="preview-scaler">
                        <?php
                        ob_start();
                        include $template;
                        echo ob_get_clean();
                        ?>
                    </div>
                </div>
                <h3><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></h3>
                <a href="?page=school-id-card-maker-generate&template=<?php echo urlencode($filename); ?>&orientation=vertical" class="button button-primary">Use Template</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
