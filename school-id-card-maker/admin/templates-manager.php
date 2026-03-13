<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$horizontal_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/horizontal/*.php' );
$vertical_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/vertical/*.php' );
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Template Library</h1>
    <hr class="wp-header-end">

    <h2>Horizontal Templates</h2>
    <div class="template-grid" style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php foreach ($horizontal_templates as $template) :
            $filename = basename($template, '.php');
        ?>
            <div class="template-card" style="border: 1px solid #ccc; padding: 10px; text-align: center; width: 200px;">
                <div class="template-preview" style="width: 100%; height: 120px; background-color: #f1f1f1; border: 1px dashed #aaa; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">
                    <span style="color: #666; font-size: 12px;">Horizontal Preview</span>
                </div>
                <h3><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></h3>
                <a href="?page=school-id-card-maker-generate&template=<?php echo urlencode($filename); ?>&orientation=horizontal" class="button button-primary">Use Template</a>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Vertical Templates</h2>
    <div class="template-grid" style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php foreach ($vertical_templates as $template) :
            $filename = basename($template, '.php');
        ?>
            <div class="template-card" style="border: 1px solid #ccc; padding: 10px; text-align: center; width: 150px;">
                <div class="template-preview" style="width: 100%; height: 180px; background-color: #f1f1f1; border: 1px dashed #aaa; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">
                    <span style="color: #666; font-size: 12px;">Vertical Preview</span>
                </div>
                <h3><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></h3>
                <a href="?page=school-id-card-maker-generate&template=<?php echo urlencode($filename); ?>&orientation=vertical" class="button button-primary">Use Template</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
