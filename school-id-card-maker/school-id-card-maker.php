<?php
/**
 * Plugin Name: School ID Card Maker
 * Description: Create and generate school student ID cards automatically.
 * Version: 3.3.0
 * Author: Neel Govind
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'SCHOOL_ID_CARD_MAKER_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCHOOL_ID_CARD_MAKER_URL', plugin_dir_url( __FILE__ ) );

// Require files
require_once SCHOOL_ID_CARD_MAKER_DIR . 'includes/database.php';
require_once SCHOOL_ID_CARD_MAKER_DIR . 'includes/student-functions.php';
require_once SCHOOL_ID_CARD_MAKER_DIR . 'includes/pdf-generator.php';

// Database Version tracking
global $school_id_card_db_version;
$school_id_card_db_version = '1.3';

// Activation Hook
register_activation_hook( __FILE__, 'school_id_card_maker_activate' );
function school_id_card_maker_activate() {
    school_id_card_maker_create_table();
}

// Database Upgrade Routine for existing users
add_action( 'plugins_loaded', 'school_id_card_maker_update_db_check' );
function school_id_card_maker_update_db_check() {
    global $school_id_card_db_version;
    if ( get_site_option( 'school_id_card_db_version' ) != $school_id_card_db_version ) {
        school_id_card_maker_create_table();
    }
}

// Global active school switcher logic
add_action('admin_init', 'school_id_card_maker_handle_global_school_switch');
function school_id_card_maker_handle_global_school_switch() {
    if (isset($_POST['global_school_switch']) && isset($_POST['global_school_id'])) {
        if (!isset($_POST['global_school_nonce']) || !wp_verify_nonce($_POST['global_school_nonce'], 'switch_global_school')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $school_id = intval($_POST['global_school_id']);
        update_user_meta(get_current_user_id(), 'school_id_card_active_school', $school_id);

        wp_redirect(remove_query_arg(array('global_school_switch', 'global_school_id', 'global_school_nonce')));
        exit;
    }
}

// Register Admin Menu
add_action( 'admin_menu', 'school_id_card_maker_admin_menu' );
function school_id_card_maker_admin_menu() {
    add_menu_page(
        'School ID Cards',
        'School ID Cards',
        'manage_options',
        'school-id-card-maker',
        'school_id_card_maker_student_list_page',
        'dashicons-id',
        25
    );

    add_submenu_page(
        'school-id-card-maker',
        'Student List',
        'Student List',
        'manage_options',
        'school-id-card-maker',
        'school_id_card_maker_student_list_page'
    );

    add_submenu_page(
        'school-id-card-maker',
        'Manage Schools',
        'Manage Schools',
        'manage_options',
        'school-id-card-maker-schools',
        'school_id_card_maker_schools_page'
    );

    add_submenu_page(
        'school-id-card-maker',
        'Add Student',
        'Add Student',
        'manage_options',
        'school-id-card-maker-add',
        'school_id_card_maker_add_student_page'
    );

    add_submenu_page(
        'school-id-card-maker',
        'Template Library',
        'Template Library',
        'manage_options',
        'school-id-card-maker-templates',
        'school_id_card_maker_templates_page'
    );

    add_submenu_page(
        'school-id-card-maker',
        'Generate ID Card',
        'Generate ID Card',
        'manage_options',
        'school-id-card-maker-generate',
        'school_id_card_maker_generate_page'
    );

    add_submenu_page(
        'school-id-card-maker',
        'ID Card Builder',
        'ID Card Builder',
        'manage_options',
        'school-id-card-maker-builder',
        'school_id_card_maker_builder_page'
    );

    add_submenu_page(
        'school-id-card-maker',
        'Settings',
        'Settings',
        'manage_options',
        'school-id-card-maker-settings',
        'school_id_card_maker_settings_page'
    );
}

// Include page logic
// Helper to render global switcher UI on top of pages
function school_id_card_maker_render_global_switcher() {
    $schools = school_id_card_maker_get_all_schools();
    $active_school_id = get_user_meta(get_current_user_id(), 'school_id_card_active_school', true);
    if (!$active_school_id) $active_school_id = 0;

    echo '<div style="background: #fff; padding: 15px 20px; margin: 15px 20px 0 0; border-radius: 8px; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
    echo '<div><strong><span class="dashicons dashicons-admin-home" style="color: #4F46E5; margin-right: 5px;"></span> Active Working Context:</strong> ' . ($active_school_id ? esc_html(school_id_card_maker_get_school($active_school_id)->school_name ?? 'Unknown') : 'Global / All Schools') . '</div>';

    echo '<form method="post" action="" style="display:flex; gap: 10px; align-items: center; margin: 0;">';
    wp_nonce_field('switch_global_school', 'global_school_nonce');
    echo '<select name="global_school_id" id="global_school_switcher" class="saas-select" style="min-width: 250px;" onchange="this.form.submit()">';
    echo '<option value="0">-- All Schools (Global) --</option>';
    foreach ($schools as $s) {
        echo '<option value="' . esc_attr($s->id) . '" ' . selected($active_school_id, $s->id, false) . '>' . esc_html($s->school_name) . '</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="global_school_switch" value="1">';
    echo '</form>';
    echo '</div>';

    // Inject Select2 for live search
    echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
    echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';
    echo '<script>
        jQuery(document).ready(function($) {
            $("#global_school_switcher").select2({
                placeholder: "Search and switch school...",
                allowClear: false
            });
        });
    </script>';
}

function school_id_card_maker_student_list_page() {
    school_id_card_maker_render_global_switcher();
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/student-list.php';
}

function school_id_card_maker_schools_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/schools-manager.php';
}

function school_id_card_maker_add_student_page() {
    school_id_card_maker_render_global_switcher();
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/student-form.php';
}

function school_id_card_maker_templates_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/templates-manager.php';
}

function school_id_card_maker_generate_page() {
    school_id_card_maker_render_global_switcher();
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/generate-card.php';
}


function school_id_card_maker_builder_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/id-card-builder.php';
}

function school_id_card_maker_settings_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/settings.php';
}

add_action('admin_post_school_id_card_maker_generate', 'school_id_card_maker_process_generation');
function school_id_card_maker_process_generation() {
    if (!isset($_POST['school_id_card_maker_generate_nonce']) || !wp_verify_nonce($_POST['school_id_card_maker_generate_nonce'], 'generate_cards')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $orientation = sanitize_file_name($_POST['orientation']);
    $template_name = sanitize_file_name($_POST['template']);
    $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'pdf';

    if (!in_array($orientation, ['horizontal', 'vertical'])) {
        wp_die('Invalid orientation.');
    }
    $class = isset($_POST['class']) ? sanitize_text_field($_POST['class']) : '';
    $section = isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
    $filter_school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;

    $is_custom_template = strpos($template_name, 'custom-') === 0;

    $template_file = '';
    $custom_template_html = '';
    if (!$is_custom_template) {
        $template_file = SCHOOL_ID_CARD_MAKER_DIR . 'templates/' . $orientation . '/' . $template_name . '.php';
        if (!file_exists($template_file)) {
            wp_die('Template not found.');
        }
    } else {
        $custom_templates = get_option('school_id_card_custom_templates', array());
        if (isset($custom_templates[$template_name])) {
            $custom_template_html = wp_unslash($custom_templates[$template_name]['html']);
        } else {
            wp_die('Custom template not found.');
        }
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
        if ($filter_school_id > 0) $args['school_id'] = $filter_school_id;
        $students = school_id_card_maker_get_students($args);
    }

    if (empty($students)) {
        wp_die('No students found for the selected criteria. <a href="javascript:history.back()">Go back</a>');
    } else {
        $html = '';
        foreach ($students as $student) {

            if (!empty($student->school_id)) {
                $school = school_id_card_maker_get_school($student->school_id);
                if ($school) {
                    $student->school_name = $school->school_name;
                    $student->school_logo = $school->school_logo;
                    $student->principal_signature = $school->principal_signature;
                    $student->school_address = $school->school_address;
                    $student->school_contact = $school->school_contact;
                    $student->school_email = $school->school_email;
                    $student->school_website = $school->school_website;
                }
            }

            if ($is_custom_template) {
                $card_html = $custom_template_html;
                $card_html = str_replace('{{STUDENT_NAME}}', esc_html($student->student_name ?? ''), $card_html);
                $s_name = !empty($student->school_name) ? $student->school_name : get_option('school_id_card_default_school_name', 'Default School');
                $card_html = str_replace('{{SCHOOL_NAME}}', esc_html($s_name), $card_html);

                $s_address = !empty($student->school_address) ? $student->school_address : get_option('school_id_card_default_school_address', '');
                $card_html = str_replace('{{SCHOOL_ADDRESS}}', esc_html($s_address), $card_html);

                $s_contact = !empty($student->school_contact) ? $student->school_contact : get_option('school_id_card_default_school_contact', '');
                $card_html = str_replace('{{SCHOOL_CONTACT}}', esc_html($s_contact), $card_html);

                $s_email = !empty($student->school_email) ? $student->school_email : get_option('school_id_card_default_school_email', '');
                $card_html = str_replace('{{SCHOOL_EMAIL}}', esc_html($s_email), $card_html);

                $card_html = str_replace('{{CLASS_INFO}}', esc_html(($student->class ?? '') . ' ' . ($student->section ?? '')), $card_html);
                $card_html = str_replace('{{ROLL_NO}}', esc_html($student->roll_no ?? ''), $card_html);
                $card_html = str_replace('{{DOB}}', esc_html($student->dob ?? ''), $card_html);
                $card_html = str_replace('{{BLOOD_GROUP}}', esc_html($student->blood_group ?? ''), $card_html);
                $card_html = str_replace('{{ADMISSION_NO}}', esc_html($student->admission_no ?? ''), $card_html);
                $card_html = str_replace('{{FATHER_NAME}}', esc_html($student->father_name ?? ''), $card_html);
                $card_html = str_replace('{{MOTHER_NAME}}', esc_html($student->mother_name ?? ''), $card_html);
                $card_html = str_replace('{{PHONE}}', esc_html($student->phone ?? ''), $card_html);
                $card_html = str_replace('{{ADDRESS}}', esc_html($student->address ?? ''), $card_html);

                if (!empty($student->student_photo)) {
                    $photo_replacement = '<img src="' . esc_url($student->student_photo) . '" style="width:100%; height:100%; object-fit:cover;" alt="Photo">';
                } else {
                    $photo_replacement = '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999; background:#eee; border:1px solid #ccc;">No Photo</div>';
                }
                $card_html = str_replace('{{STUDENT_PHOTO}}', $photo_replacement, $card_html);

                $sig_url = !empty($student->principal_signature) ? $student->principal_signature : get_option("school_id_card_default_principal_signature", "");
                if (!empty($sig_url)) {
                    $sig_replacement = '<img src="' . esc_url($sig_url) . '" style="width:100%; height:100%; object-fit:contain;" alt="Principal Signature">';
                } else {
                    $sig_replacement = '';
                }
                $card_html = str_replace('{{PRINCIPAL_SIGNATURE}}', $sig_replacement, $card_html);

                $html .= $card_html;
            } else {
                ob_start();
                include $template_file;
                $html .= ob_get_clean();
            }
            $html .= '<div class="page-break"></div>';
        }

        $html = preg_replace('/<div class="page-break"><\/div>$/', '', $html);

        // Get global typography overrides
        $global_font = get_option('school_id_card_global_font', 'Helvetica, Arial, sans-serif');
        $global_size = get_option('school_id_card_global_size', '11px');

        // Strip tags but keep quotes intact for CSS
        $safe_font = wp_strip_all_tags($global_font);
        $safe_size = wp_strip_all_tags($global_size);

        $typography_css = '
        .id-card { font-family: ' . $safe_font . ' !important; }
        .id-card table, .id-card p, .id-card td { font-size: ' . $safe_size . ' !important; }
        ';

        if ($format === 'pdf') {
            // Inject typography css into the generated html string before passing to dompdf
            $html_with_typography = '<style>' . $typography_css . '</style>' . $html;
            school_id_card_maker_generate_pdf($html_with_typography, $orientation, 'id-cards.pdf');
        } else if ($format === 'png') {
            $css_path = SCHOOL_ID_CARD_MAKER_DIR . 'assets/css/id-card.css';
            $css_content = file_exists($css_path) ? file_get_contents($css_path) : '';
            $css_content .= $typography_css;

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
            $css_path = SCHOOL_ID_CARD_MAKER_DIR . 'assets/css/id-card.css';
            $css_content = file_exists($css_path) ? file_get_contents($css_path) : '';
            $css_content .= $typography_css;

            $page_css = '';
            if ($orientation === 'horizontal') {
                $page_css = '@page { size: 350px 220px; margin: 0; }';
            } else {
                $page_css = '@page { size: 220px 350px; margin: 0; }';
            }

            echo '<!DOCTYPE html><html><head><title>Print ID Cards</title><style>
            body { font-family: sans-serif; background: #fff; margin: 0; padding: 0; }
            .page-break { page-break-after: always; clear: both; }
            .id-card { margin: 0 !important; border: none !important; box-shadow: none !important; }
            @media print {
                body { padding: 0; margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .no-print { display: none !important; }
                ' . $page_css . '
            }
            ' . $css_content . '
            </style></head><body>';
            echo '<div class="no-print" style="margin-bottom: 20px; padding: 20px; background: #f1f1f1; border-bottom: 1px solid #ccc; text-align: center;">
                    <button onclick="window.print();" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #4F46E5; color: #fff; border: none; border-radius: 5px;">Print Now</button>
                    <button onclick="window.history.back();" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px; background: #fff; border: 1px solid #ccc; border-radius: 5px;">Back</button>
                  </div>';
            echo $html;
            echo '<script>window.onload = function() { setTimeout(function() { window.print(); }, 500); }</script>';
            echo '</body></html>';
            exit;
        }
    }
}

// Enqueue styles
add_action('admin_enqueue_scripts', 'school_id_card_maker_admin_scripts');
function school_id_card_maker_admin_scripts($hook) {
    if (strpos($hook, 'school-id-card-maker') !== false) {
        wp_enqueue_style('google-fonts-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap', array(), null);
        wp_enqueue_style('school-id-card-maker-css', SCHOOL_ID_CARD_MAKER_URL . 'assets/css/id-card.css');
        wp_enqueue_style('school-id-card-maker-saas-css', SCHOOL_ID_CARD_MAKER_URL . 'assets/css/admin-saas.css');
    }
}
