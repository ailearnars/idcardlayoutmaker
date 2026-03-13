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

<div class="wrap saas-wrap">
    <h1>Template Library</h1>

    <div style="margin-top: 32px;">
        <h2>Horizontal Templates</h2>
        <div class="saas-template-grid">
            <?php foreach ($horizontal_templates as $template) :
                $filename = basename($template, '.php');
            ?>
                <div class="saas-template-card">
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
                    <a href="?page=school-id-card-maker-generate&template=<?php echo urlencode($filename); ?>&orientation=horizontal" class="saas-btn saas-btn-primary" style="width: 100%;">Use Template</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="margin-top: 48px;">
        <h2>Vertical Templates</h2>
        <div class="saas-template-grid" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));">
            <?php foreach ($vertical_templates as $template) :
                $filename = basename($template, '.php');
            ?>
                <div class="saas-template-card">
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
                    <a href="?page=school-id-card-maker-generate&template=<?php echo urlencode($filename); ?>&orientation=vertical" class="saas-btn saas-btn-primary" style="width: 100%;">Use Template</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
