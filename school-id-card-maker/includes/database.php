<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function school_id_card_maker_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'school_students';
    $charset_collate = $wpdb->get_charset_collate();

    $schools_table = $wpdb->prefix . 'school_id_schools';

    $sql_schools = "CREATE TABLE $schools_table (
        id int(9) NOT NULL AUTO_INCREMENT,
        school_name varchar(255) NOT NULL,
        school_logo text,
        principal_signature text,
        school_address text,
        school_contact varchar(100),
        school_email varchar(100),
        school_website varchar(255),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        school_id int(9) DEFAULT 0,
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
        principal_signature text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_schools );
    dbDelta( $sql );

    // Explicit alter table to ensure school_website exists due to dbDelta caching issues sometimes
    $row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$schools_table' AND column_name = 'school_website'" );
    if (empty($row)) {
        $wpdb->query("ALTER TABLE $schools_table ADD school_website varchar(255) DEFAULT NULL");
    }

    $row_sig = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$schools_table' AND column_name = 'principal_signature'" );
    if (empty($row_sig)) {
        $wpdb->query("ALTER TABLE $schools_table ADD principal_signature text DEFAULT NULL");
    }

    global $school_id_card_db_version;
    update_option( 'school_id_card_db_version', $school_id_card_db_version );
}
