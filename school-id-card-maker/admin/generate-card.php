<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get available templates
$horizontal_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/horizontal/*.php' );
$vertical_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/vertical/*.php' );

$custom_templates_option = get_option('school_id_card_custom_templates', array());
$custom_horizontal = array();
$custom_vertical = array();
if (is_array($custom_templates_option)) {
    foreach ($custom_templates_option as $id => $tpl) {
        if ($tpl['orientation'] === 'horizontal') $custom_horizontal[] = $id;
        else $custom_vertical[] = $id;
    }
}

$selected_template = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : '';
$selected_orientation = isset($_GET['orientation']) ? sanitize_text_field($_GET['orientation']) : 'horizontal';
$selected_student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
?>

<div class="wrap saas-wrap">
    <h1>Generate ID Cards</h1>

    <div class="saas-card" style="max-width: 600px;">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="school_id_card_maker_generate">
            <?php wp_nonce_field('generate_cards', 'school_id_card_maker_generate_nonce'); ?>

            <div class="saas-grid-2">
                <div class="saas-form-group">
                    <label for="orientation">Orientation</label>
                    <select name="orientation" id="orientation" class="saas-select">
                        <option value="horizontal" <?php selected($selected_orientation, 'horizontal'); ?>>Horizontal</option>
                        <option value="vertical" <?php selected($selected_orientation, 'vertical'); ?>>Vertical</option>
                    </select>
                </div>

                <div class="saas-form-group">
                    <label for="template">Template</label>
                    <select name="template" id="template" class="saas-select">
                        <optgroup label="My Custom Horizontal">
                            <?php foreach ($custom_horizontal as $id) : ?>
                                <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_template, $id); ?>><?php echo esc_html($id); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="My Custom Vertical">
                            <?php foreach ($custom_vertical as $id) : ?>
                                <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_template, $id); ?>><?php echo esc_html($id); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Default Horizontal">
                            <?php foreach ($horizontal_templates as $template) :
                                $filename = basename($template, '.php');
                            ?>
                                <option value="<?php echo esc_attr($filename); ?>" <?php selected($selected_template, $filename); ?>><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Default Vertical">
                            <?php foreach ($vertical_templates as $template) :
                                $filename = basename($template, '.php');
                            ?>
                                <option value="<?php echo esc_attr($filename); ?>" <?php selected($selected_template, $filename); ?>><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>

            <div style="border-top: 1px solid var(--saas-border); margin: 20px 0;"></div>

            <?php if ($selected_student_id > 0) : ?>
                <div class="saas-form-group">
                    <label>Student ID (Single Generate)</label>
                    <input type="number" name="student_id" value="<?php echo esc_attr($selected_student_id); ?>" readonly class="saas-input" style="background: #f9fafb; cursor: not-allowed;">
                    <p class="description" style="margin-top: 4px; font-size: 12px; color: var(--saas-text-muted);">Generating for single student.</p>
                </div>
            <?php else : ?>
                <h2>Bulk Generate Filters</h2>
                <div class="saas-grid-2">
                    <div class="saas-form-group">
                        <label for="school_id">Filter by School</label>
                        <select name="school_id" id="school_id" class="saas-select">
                            <option value="">All Schools</option>
                            <?php
                            $schools = school_id_card_maker_get_all_schools();
                            foreach ($schools as $s) {
                                echo '<option value="' . esc_attr($s->id) . '">' . esc_html($s->school_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="saas-grid-2">
                    <div class="saas-form-group">
                        <label for="class">Class</label>
                        <input type="text" name="class" id="class" class="saas-input" placeholder="e.g. 10">
                        <p class="description" style="margin-top: 4px; font-size: 12px; color: var(--saas-text-muted);">Leave empty for all classes.</p>
                    </div>
                    <div class="saas-form-group">
                        <label for="section">Section</label>
                        <input type="text" name="section" id="section" class="saas-input" placeholder="e.g. A">
                        <p class="description" style="margin-top: 4px; font-size: 12px; color: var(--saas-text-muted);">Leave empty for all sections.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div style="border-top: 1px solid var(--saas-border); margin: 20px 0;"></div>

            <div class="saas-form-group">
                <label>Export Format</label>
                <div class="saas-radio-group">
                    <label class="saas-radio-label">
                        <input type="radio" name="format" value="pdf" checked>
                        <span><strong>PDF Output</strong> - Generates a multi-page PDF document.</span>
                    </label>
                    <label class="saas-radio-label">
                        <input type="radio" name="format" value="png">
                        <span><strong>PNG Output</strong> - Downloads a ZIP archive of individual images.</span>
                    </label>
                    <label class="saas-radio-label">
                        <input type="radio" name="format" value="print">
                        <span><strong>Print in Browser</strong> - Opens native print dialog immediately.</span>
                    </label>
                </div>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" name="generate_id_cards" class="saas-btn saas-btn-primary" style="padding: 12px 24px; font-size: 15px; width: 100%;">Generate ID Cards</button>
            </div>
        </form>
    </div>
</div>
