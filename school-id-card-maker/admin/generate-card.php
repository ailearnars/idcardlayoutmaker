<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_id_cards'])) {
    if (!isset($_POST['school_id_card_maker_generate_nonce']) || !wp_verify_nonce($_POST['school_id_card_maker_generate_nonce'], 'generate_cards')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $orientation = sanitize_file_name($_POST['orientation']);
    $template_name = sanitize_file_name($_POST['template']);
    $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'pdf';

    // Validate orientation explicitly
    if (!in_array($orientation, ['horizontal', 'vertical'])) {
        wp_die('Invalid orientation.');
    }
    $class = isset($_POST['class']) ? sanitize_text_field($_POST['class']) : '';
    $section = isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;

    $template_file = SCHOOL_ID_CARD_MAKER_DIR . 'templates/' . $orientation . '/' . $template_name . '.php';

    if (!file_exists($template_file)) {
        wp_die('Template not found.');
    }

    $students = array();

    if ($student_id > 0) {
        $student = school_id_card_maker_get_student($student_id);
        if ($student) {
            $students[] = $student;
        }
    } else {
        $args = array();
        if (!empty($class)) $args['class'] = $class;
        if (!empty($section)) $args['section'] = $section;
        $students = school_id_card_maker_get_students($args);
    }

    if (empty($students)) {
        echo '<div class="notice notice-error"><p>No students found for the selected criteria.</p></div>';
    } else {
        $html = '';
        foreach ($students as $student) {
            ob_start();
            include $template_file;
            $html .= ob_get_clean();
            $html .= '<div class="page-break"></div>'; // Add page break between cards
        }

        // Remove last page break
        $html = preg_replace('/<div class="page-break"><\/div>$/', '', $html);

        if ($format === 'pdf') {
            // Output PDF
            school_id_card_maker_generate_pdf($html, $orientation, 'id-cards.pdf');
        } else if ($format === 'png') {
            // Read CSS
            $css_path = SCHOOL_ID_CARD_MAKER_DIR . 'assets/css/id-card.css';
            $css_content = file_exists($css_path) ? file_get_contents($css_path) : '';

            // Include HTML2Canvas and display cards directly for rendering
            echo '<html><head><style>' . $css_content . '</style></head><body>';
            echo '<h2>Preparing PNG Download...</h2>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>';
            echo '<div id="png-export-container">' . $html . '</div>';
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    let container = document.getElementById("png-export-container");
                    let cards = container.querySelectorAll(".id-card");
                    let promises = [];

                    cards.forEach((card, index) => {
                        promises.push(
                            html2canvas(card, {scale: 2, useCORS: true}).then(canvas => {
                                let link = document.createElement("a");
                                link.download = "id-card-" + (index + 1) + ".png";
                                link.href = canvas.toDataURL("image/png");
                                link.click();
                            })
                        );
                    });

                    Promise.all(promises).then(() => {
                        alert("PNG download initiated for " + cards.length + " cards.");
                    });
                });
            </script></body></html>';
            exit;
        } else if ($format === 'print') {
            // Read CSS
            $css_path = SCHOOL_ID_CARD_MAKER_DIR . 'assets/css/id-card.css';
            $css_content = file_exists($css_path) ? file_get_contents($css_path) : '';

            // Direct browser print rendering
            echo '<!DOCTYPE html><html><head><title>Print ID Cards</title><style>
            body { font-family: sans-serif; background: #fff; margin: 0; padding: 20px; }
            .page-break { page-break-after: always; clear: both; margin-bottom: 20px; }
            @media print {
                body { padding: 0; }
                .no-print { display: none; }
            }
            ' . $css_content . '
            </style></head><body>';
            echo '<div class="no-print" style="margin-bottom: 20px;">
                    <button onclick="window.print();" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print Now</button>
                    <button onclick="window.history.back();" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">Back</button>
                  </div>';
            echo $html;
            echo '<script>window.onload = function() { window.print(); }</script>';
            echo '</body></html>';
            exit;
        }
    }
}

// Get available templates
$horizontal_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/horizontal/*.php' );
$vertical_templates = glob( SCHOOL_ID_CARD_MAKER_DIR . 'templates/vertical/*.php' );

$selected_template = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : '';
$selected_orientation = isset($_GET['orientation']) ? sanitize_text_field($_GET['orientation']) : 'horizontal';
$selected_student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Generate ID Cards</h1>
    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('generate_cards', 'school_id_card_maker_generate_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="orientation">Orientation</label></th>
                <td>
                    <select name="orientation" id="orientation">
                        <option value="horizontal" <?php selected($selected_orientation, 'horizontal'); ?>>Horizontal</option>
                        <option value="vertical" <?php selected($selected_orientation, 'vertical'); ?>>Vertical</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="template">Template</label></th>
                <td>
                    <select name="template" id="template">
                        <optgroup label="Horizontal">
                            <?php foreach ($horizontal_templates as $template) :
                                $filename = basename($template, '.php');
                            ?>
                                <option value="<?php echo esc_attr($filename); ?>" <?php selected($selected_template, $filename); ?>><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Vertical">
                            <?php foreach ($vertical_templates as $template) :
                                $filename = basename($template, '.php');
                            ?>
                                <option value="<?php echo esc_attr($filename); ?>" <?php selected($selected_template, $filename); ?>><?php echo esc_html(ucwords(str_replace('-', ' ', $filename))); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </td>
            </tr>

            <?php if ($selected_student_id > 0) : ?>
                <tr>
                    <th scope="row"><label>Student ID</label></th>
                    <td>
                        <input type="number" name="student_id" value="<?php echo esc_attr($selected_student_id); ?>" readonly>
                        <p class="description">Generating for single student.</p>
                    </td>
                </tr>
            <?php else : ?>
                <tr>
                    <th scope="row"><label for="class">Class (Bulk Generate)</label></th>
                    <td>
                        <input type="text" name="class" id="class" class="regular-text">
                        <p class="description">Leave empty to generate for all classes.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="section">Section (Bulk Generate)</label></th>
                    <td>
                        <input type="text" name="section" id="section" class="regular-text">
                        <p class="description">Leave empty to generate for all sections.</p>
                    </td>
                </tr>
            <?php endif; ?>

            <tr>
                <th scope="row"><label>Export Format</label></th>
                <td>
                    <p><label><input type="radio" name="format" value="pdf" checked> PDF</label></p>
                    <p><label><input type="radio" name="format" value="png"> PNG (Image Archive)</label></p>
                    <p><label><input type="radio" name="format" value="print"> Print Directly in Browser</label></p>
                </td>
            </tr>
        </table>

        <div style="display: flex; gap: 10px; align-items: center;">
            <?php submit_button('Generate ID Cards', 'primary', 'generate_id_cards', false); ?>
        </div>
    </form>
</div>
