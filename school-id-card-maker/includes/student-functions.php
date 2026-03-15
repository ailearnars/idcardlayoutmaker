<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function school_id_card_maker_get_students($args = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';

    $where = "1=1";
    if (!empty($args['class'])) {
        $where .= $wpdb->prepare(" AND class = %s", $args['class']);
    }
    if (!empty($args['section'])) {
        $where .= $wpdb->prepare(" AND section = %s", $args['section']);
    }
    if (!empty($args['school_id'])) {
        $where .= $wpdb->prepare(" AND school_id = %d", $args['school_id']);
    }
    if (!empty($args['search'])) {
        $like = '%' . $wpdb->esc_like($args['search']) . '%';
        $where .= $wpdb->prepare(" AND (student_name LIKE %s OR roll_no LIKE %s OR admission_no LIKE %s)", $like, $like, $like);
    }

    $query = "SELECT * FROM $table_name WHERE $where ORDER BY id DESC";

    if (!empty($args['limit'])) {
        $offset = !empty($args['offset']) ? intval($args['offset']) : 0;
        $limit = intval($args['limit']);
        $query .= " LIMIT $offset, $limit";
    }

    return $wpdb->get_results($query);
}

function school_id_card_maker_get_students_count($args = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';

    $where = "1=1";
    if (!empty($args['class'])) {
        $where .= $wpdb->prepare(" AND class = %s", $args['class']);
    }
    if (!empty($args['section'])) {
        $where .= $wpdb->prepare(" AND section = %s", $args['section']);
    }
    if (!empty($args['school_id'])) {
        $where .= $wpdb->prepare(" AND school_id = %d", $args['school_id']);
    }
    if (!empty($args['search'])) {
        $like = '%' . $wpdb->esc_like($args['search']) . '%';
        $where .= $wpdb->prepare(" AND (student_name LIKE %s OR roll_no LIKE %s OR admission_no LIKE %s)", $like, $like, $like);
    }

    return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
}

function school_id_card_maker_get_unique_classes($school_id = 0) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';
    $where = "1=1";
    if ($school_id > 0) {
        $where .= $wpdb->prepare(" AND school_id = %d", $school_id);
    }
    return $wpdb->get_col("SELECT DISTINCT class FROM $table_name WHERE $where ORDER BY class ASC");
}

function school_id_card_maker_get_student($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
}

function school_id_card_maker_add_student($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';

    $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');

    $wpdb->insert($table_name, $data, $format);
    return $wpdb->insert_id;
}

function school_id_card_maker_update_student($id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';

    $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
    $where = array('id' => $id);
    $where_format = array('%d');

    return $wpdb->update($table_name, $data, $where, $format, $where_format);
}

function school_id_card_maker_delete_student($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';
    return $wpdb->delete($table_name, array('id' => $id), array('%d'));
}

// -------------------
// Schools Functions
// -------------------

function school_id_card_maker_get_all_schools() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_id_schools';
    return $wpdb->get_results("SELECT * FROM $table_name ORDER BY school_name ASC");
}

function school_id_card_maker_get_school($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_id_schools';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
}

function school_id_card_maker_add_school($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_id_schools';
    // school_name, school_logo, school_address, school_contact, school_email, school_website
    $format = array('%s', '%s', '%s', '%s', '%s', '%s');
    $wpdb->insert($table_name, $data, $format);
    // Explicitly show error for debugging if insert fails
    // if ($wpdb->last_error) { error_log("DB Error: " . $wpdb->last_error); }
    return $wpdb->insert_id;
}

function school_id_card_maker_update_school($id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_id_schools';
    $format = array('%s', '%s', '%s', '%s', '%s', '%s');
    $where = array('id' => $id);
    $where_format = array('%d');
    return $wpdb->update($table_name, $data, $where, $format, $where_format);
}

function school_id_card_maker_delete_school($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_id_schools';
    return $wpdb->delete($table_name, array('id' => $id), array('%d'));
}

/**
 * Handles image upload, converts to WebP, and returns the URL.
 */
function school_id_card_maker_handle_image_upload($file_array) {
    if (empty($file_array['name']) || empty($file_array['tmp_name'])) {
        return false;
    }

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($file_array, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $file_path = $movefile['file'];
        $file_url = $movefile['url'];
        $mime_type = $movefile['type'];

        // Convert to WebP if possible
        if (function_exists('imagewebp') && in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif'])) {
            $image = null;
            if ($mime_type == 'image/jpeg') {
                $image = @imagecreatefromjpeg($file_path);
            } elseif ($mime_type == 'image/png') {
                $image = @imagecreatefrompng($file_path);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
            } elseif ($mime_type == 'image/gif') {
                $image = @imagecreatefromgif($file_path);
            }

            if ($image) {
                $webp_path = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $file_path);
                $webp_url = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $file_url);

                if (imagewebp($image, $webp_path, 80)) {
                    imagedestroy($image);
                    // Optionally delete the original file
                    @unlink($file_path);
                    return esc_url_raw($webp_url);
                }
                imagedestroy($image);
            }
        }

        return esc_url_raw($file_url);
    }
    return false;
}
