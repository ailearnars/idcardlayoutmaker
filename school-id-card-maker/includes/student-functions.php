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

    return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
}

function school_id_card_maker_get_student($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
}

function school_id_card_maker_add_student($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';

    $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');

    $wpdb->insert($table_name, $data, $format);
    return $wpdb->insert_id;
}

function school_id_card_maker_update_student($id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';

    $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
    $where = array('id' => $id);
    $where_format = array('%d');

    return $wpdb->update($table_name, $data, $where, $format, $where_format);
}

function school_id_card_maker_delete_student($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'school_students';
    return $wpdb->delete($table_name, array('id' => $id), array('%d'));
}
