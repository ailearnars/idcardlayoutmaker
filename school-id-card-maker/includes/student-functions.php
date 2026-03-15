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
// Image Upload & Compression Functions
// -------------------

function school_id_card_maker_handle_image_upload($file) {
    if (empty($file['name'])) {
        return new WP_Error('empty_file', 'No file uploaded.');
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $overrides = array('test_form' => false);

    // Attempt standard WP upload first
    $movefile = wp_handle_upload($file, $overrides);

    if ($movefile && !isset($movefile['error'])) {
        $file_path = $movefile['file'];
        $file_url = $movefile['url'];
        $file_type = wp_check_filetype($file_path, null);

        // Process Image: Resize if > 2000px, convert to WebP
        $editor = wp_get_image_editor($file_path);

        if (!is_wp_error($editor)) {
            $size = $editor->get_size();
            $changed = false;

            // Compress / Resize if width or height is greater than 2000px
            if ($size['width'] > 2000 || $size['height'] > 2000) {
                $editor->resize(2000, 2000, false);
                $changed = true;
            }

            // Convert to WebP
            $path_info = pathinfo($file_path);
            if (isset($path_info['extension']) && strtolower($path_info['extension']) !== 'webp') {
                $webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
                $saved = $editor->save($webp_path, 'image/webp');

                if (!is_wp_error($saved)) {
                    // Update variables to point to the new webp file
                    // Unlink the old non-webp file to save space
                    @unlink($file_path);

                    $file_path = $saved['path'];
                    // Construct new URL by replacing the old file basename with the new webp basename
                    $file_url = str_replace(wp_basename($movefile['file']), wp_basename($saved['path']), $movefile['url']);
                    $changed = true;
                }
            } elseif ($changed) {
                // If it was already a webp but we resized it, just save over the old file
                $editor->save($file_path);
            }
        }

        return array(
            'file' => $file_path,
            'url'  => $file_url,
            'type' => 'image/webp' // After processing we prefer webp
        );
    } else {
        return new WP_Error('upload_error', $movefile['error']);
    }
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
