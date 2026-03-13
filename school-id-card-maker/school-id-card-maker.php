<?php
/**
 * Plugin Name: School ID Card Maker
 * Description: Create and generate school student ID cards automatically.
 * Version: 2.0.0
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

// Activation Hook
register_activation_hook( __FILE__, 'school_id_card_maker_activate' );
function school_id_card_maker_activate() {
    school_id_card_maker_create_table();
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
function school_id_card_maker_student_list_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/student-list.php';
}

function school_id_card_maker_add_student_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/student-form.php';
}

function school_id_card_maker_templates_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/templates-manager.php';
}

function school_id_card_maker_generate_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/generate-card.php';
}

function school_id_card_maker_builder_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/id-card-builder.php';
}

function school_id_card_maker_settings_page() {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'admin/settings.php';
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
