<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function school_id_card_maker_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'school_students';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        student_name varchar(255) NOT NULL,
        student_photo text,
        admission_no varchar(100) NOT NULL,
        class varchar(50) NOT NULL,
        section varchar(50),
        roll_no varchar(50),
        dob date,
        blood_group varchar(10),
        phone varchar(20),
        address text,
        father_name varchar(255),
        mother_name varchar(255),
        school_name varchar(255),
        school_logo text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
