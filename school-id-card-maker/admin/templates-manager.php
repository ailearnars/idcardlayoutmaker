<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$horizontal_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/horizontal/*.php' );
$vertical_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/vertical/*.php' );

$custom_templates_option = get_option('school_id_card_custom_templates', array());
$custom_horizontal = array();
$custom_vertical = array();

if (is_array($custom_templates_option)) {
    foreach ($custom_templates_option as $id => $tpl) {
        if ($tpl['orientation'] === 'horizontal') {
            $custom_horizontal[] = $tpl;
        } else {
            $custom_vertical[] = $tpl;
        }
    }
}

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

<?php
// Handle saving global typography settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_typography'])) {
    if (isset($_POST['typography_nonce']) && wp_verify_nonce($_POST['typography_nonce'], 'save_typography_action') && current_user_can('manage_options')) {
        update_option('school_id_card_global_font', sanitize_text_field($_POST['global_font']));
        update_option('school_id_card_global_size', sanitize_text_field($_POST['global_size']));
        echo '<div class="saas-notice saas-notice-success"><p>Typography settings saved successfully.</p></div>';
    }
}

$global_font = get_option('school_id_card_global_font', 'Helvetica, Arial, sans-serif');
$global_size = get_option('school_id_card_global_size', '11px');

// Strip tags but keep quotes intact for CSS
$safe_font = wp_strip_all_tags($global_font);
$safe_size = wp_strip_all_tags($global_size);

// Build CSS var injector for preview
$preview_style = '<style>.preview-scaler .id-card { font-family: ' . $safe_font . ' !important; } .preview-scaler .id-card table, .preview-scaler .id-card p, .preview-scaler .id-card td { font-size: ' . $safe_size . ' !important; }</style>';
?>

<div class="wrap saas-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--saas-border); padding-bottom: 16px; margin-bottom: 24px;">
        <h1 style="border: none; margin: 0; padding: 0;">Template Library</h1>
    </div>

    <!-- Typography Controls -->
    <div class="saas-card" style="margin-bottom: 32px; background: #f9fafb;">
        <form method="post" action="" style="display: flex; gap: 20px; align-items: flex-end;">
            <?php wp_nonce_field('save_typography_action', 'typography_nonce'); ?>
            <div class="saas-form-group" style="margin: 0; flex: 1;">
                <label>Global Template Font</label>
                <select name="global_font" class="saas-select" id="preview_font" onchange="updatePreviewTypography()">
                    <option value="'Helvetica Neue', Helvetica, Arial, sans-serif" <?php selected($global_font, "'Helvetica Neue', Helvetica, Arial, sans-serif"); ?>>Helvetica / Arial</option>
                    <option value="'Times New Roman', Times, serif" <?php selected($global_font, "'Times New Roman', Times, serif"); ?>>Times New Roman</option>
                    <option value="'Courier New', Courier, monospace" <?php selected($global_font, "'Courier New', Courier, monospace"); ?>>Courier New</option>
                    <option value="Tahoma, Geneva, sans-serif" <?php selected($global_font, "Tahoma, Geneva, sans-serif"); ?>>Tahoma</option>
                    <option value="'Trebuchet MS', Helvetica, sans-serif" <?php selected($global_font, "'Trebuchet MS', Helvetica, sans-serif"); ?>>Trebuchet MS</option>
                    <option value="'Alice', serif" <?php selected($global_font, "'Alice', serif"); ?>>Alice</option>
                    <option value="'Roboto', sans-serif" <?php selected($global_font, "'Roboto', sans-serif"); ?>>Roboto</option>
                    <option value="'Open Sans', sans-serif" <?php selected($global_font, "'Open Sans', sans-serif"); ?>>Open Sans</option>
                    <option value="'Lato', sans-serif" <?php selected($global_font, "'Lato', sans-serif"); ?>>Lato</option>
                    <option value="'Montserrat', sans-serif" <?php selected($global_font, "'Montserrat', sans-serif"); ?>>Montserrat</option>
                </select>
            </div>
            <div class="saas-form-group" style="margin: 0; flex: 1;">
                <label>Global Text Size</label>
                <select name="global_size" class="saas-select" id="preview_size" onchange="updatePreviewTypography()">
                    <option value="9px" <?php selected($global_size, "9px"); ?>>Small (9px)</option>
                    <option value="10px" <?php selected($global_size, "10px"); ?>>Medium (10px)</option>
                    <option value="11px" <?php selected($global_size, "11px"); ?>>Standard (11px)</option>
                    <option value="12px" <?php selected($global_size, "12px"); ?>>Large (12px)</option>
                    <option value="13px" <?php selected($global_size, "13px"); ?>>Extra Large (13px)</option>
                </select>
            </div>
            <div>
                <button type="submit" name="save_typography" class="saas-btn saas-btn-primary">Save Typography</button>
            </div>
        </form>
    </div>

    <script>
        function updatePreviewTypography() {
            const font = document.getElementById('preview_font').value;
            const size = document.getElementById('preview_size').value;

            const cards = document.querySelectorAll('.preview-scaler .id-card');
            cards.forEach(card => {
                card.style.setProperty('font-family', font, 'important');

                const texts = card.querySelectorAll('td, p, span, div');
                texts.forEach(t => {
                    // avoid changing header/name sizes which are specifically styled larger
                    if (!t.classList.contains('school-name') && !t.classList.contains('name')) {
                        t.style.setProperty('font-size', size, 'important');
                    }
                });
            });
        }

        // Run on load
        document.addEventListener("DOMContentLoaded", updatePreviewTypography);
    </script>

    <?php echo $preview_style; ?>

    <?php if (!empty($custom_horizontal) || !empty($custom_vertical)): ?>
    <div style="margin-top: 32px; background: #e0e7ff; padding: 20px; border-radius: 8px; border: 1px solid #c7d2fe;">
        <h2>My Custom Templates</h2>
        <div class="saas-template-grid">
            <?php foreach ($custom_horizontal as $tpl) : ?>
                <div class="saas-template-card">
                    <div class="preview-container preview-horizontal">
                        <div class="preview-scaler">
                            <?php
                            $card_html = wp_unslash($tpl['html']);
                            $card_html = str_replace('{{STUDENT_NAME}}', 'John Doe', $card_html);
                            $card_html = str_replace('{{CLASS_INFO}}', '10 A', $card_html);
                            $card_html = str_replace('{{ROLL_NO}}', '1234', $card_html);
                            $card_html = str_replace('{{DOB}}', '2005-08-15', $card_html);
                            $card_html = str_replace('{{STUDENT_PHOTO}}', '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999; background:#eee; border:1px solid #ccc;">Photo</div>', $card_html);

                            $allowed_html = wp_kses_allowed_html('post');
                            $allowed_html['div']['style'] = true;
                            $allowed_html['span']['style'] = true;
                            $allowed_html['p']['style'] = true;
                            $allowed_html['h1']['style'] = true;
                            $allowed_html['h2']['style'] = true;
                            $allowed_html['h3']['style'] = true;
                            $allowed_html['img']['style'] = true;

                            echo wp_kses($card_html, $allowed_html);
                            ?>
                        </div>
                    </div>
                    <h3><?php echo esc_html($tpl['id']); ?> (Horizontal)</h3>
                    <a href="?page=school-id-card-maker-generate&template=<?php echo esc_attr($tpl['id']); ?>&orientation=horizontal" class="saas-btn saas-btn-primary" style="width: 100%;">Use Template</a>
                </div>
            <?php endforeach; ?>
            <?php foreach ($custom_vertical as $tpl) : ?>
                <div class="saas-template-card">
                    <div class="preview-container preview-vertical">
                        <div class="preview-scaler">
                            <?php
                            $card_html = wp_unslash($tpl['html']);
                            $card_html = str_replace('{{STUDENT_NAME}}', 'John Doe', $card_html);
                            $card_html = str_replace('{{CLASS_INFO}}', '10 A', $card_html);
                            $card_html = str_replace('{{ROLL_NO}}', '1234', $card_html);
                            $card_html = str_replace('{{DOB}}', '2005-08-15', $card_html);
                            $card_html = str_replace('{{STUDENT_PHOTO}}', '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999; background:#eee; border:1px solid #ccc;">Photo</div>', $card_html);

                            echo wp_kses($card_html, $allowed_html);
                            ?>
                        </div>
                    </div>
                    <h3><?php echo esc_html($tpl['id']); ?> (Vertical)</h3>
                    <a href="?page=school-id-card-maker-generate&template=<?php echo esc_attr($tpl['id']); ?>&orientation=vertical" class="saas-btn saas-btn-primary" style="width: 100%;">Use Template</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div style="margin-top: 48px;">
        <h2>Default Horizontal Templates</h2>
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
        <h2>Default Vertical Templates</h2>
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
