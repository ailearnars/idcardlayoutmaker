<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_student')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $id = intval($_GET['id']);
    school_id_card_maker_delete_student($id);
    echo '<div class="notice notice-success is-dismissible"><p>Student deleted successfully.</p></div>';
}

$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$limit = 50;
$offset = ($paged - 1) * $limit;

$args = array(
    'limit'  => $limit,
    'offset' => $offset
);

$students = school_id_card_maker_get_students($args);
$total_students = school_id_card_maker_get_students_count();
$total_pages = ceil($total_students / $limit);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Student List</h1>
    <a href="?page=school-id-card-maker-add" class="page-title-action">Add New Student</a>
    <hr class="wp-header-end">

    <div class="tablenav top">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo esc_html($total_students); ?> items</span>
            <?php if ($total_pages > 1) : ?>
                <span class="pagination-links">
                    <?php
                    $page_links = paginate_links(array(
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total'     => $total_pages,
                        'current'   => $paged,
                    ));
                    echo $page_links;
                    ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped table-view-list students">
        <thead>
            <tr>
                <th scope="col" id="id" class="manage-column column-id column-primary sortable desc">
                    <a href="#"><span>ID</span></a>
                </th>
                <th scope="col" id="student_name" class="manage-column column-student_name sortable desc">
                    <a href="#"><span>Name</span></a>
                </th>
                <th scope="col" id="admission_no" class="manage-column column-admission_no sortable desc">
                    <a href="#"><span>Admission No</span></a>
                </th>
                <th scope="col" id="class" class="manage-column column-class sortable desc">
                    <a href="#"><span>Class</span></a>
                </th>
                <th scope="col" id="section" class="manage-column column-section sortable desc">
                    <a href="#"><span>Section</span></a>
                </th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php if (!empty($students)) : ?>
                <?php foreach ($students as $student) : ?>
                    <tr id="student-<?php echo esc_attr($student->id); ?>" class="iedit author-self level-0 type-student has-row-actions">
                        <td class="id column-id has-row-actions column-primary" data-colname="ID">
                            <?php echo esc_html($student->id); ?>
                            <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
                        </td>
                        <td class="student_name column-student_name" data-colname="Name">
                            <strong><a class="row-title" href="?page=school-id-card-maker-add&id=<?php echo esc_attr($student->id); ?>"><?php echo esc_html($student->student_name); ?></a></strong>
                            <div class="row-actions">
                                <span class="edit"><a href="?page=school-id-card-maker-add&id=<?php echo esc_attr($student->id); ?>">Edit</a> | </span>
                                <span class="delete"><a href="<?php echo wp_nonce_url("?page=school-id-card-maker&action=delete&id={$student->id}", 'delete_student'); ?>" class="submitdelete" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a> | </span>
                                <span class="generate"><a href="?page=school-id-card-maker-generate&action=single&student_id=<?php echo esc_attr($student->id); ?>">Generate ID</a> | </span>
                                <span class="print"><a href="#" onclick="window.print(); return false;">Print</a></span>
                            </div>
                        </td>
                        <td class="admission_no column-admission_no" data-colname="Admission No"><?php echo esc_html($student->admission_no); ?></td>
                        <td class="class column-class" data-colname="Class"><?php echo esc_html($student->class); ?></td>
                        <td class="section column-section" data-colname="Section"><?php echo esc_html($student->section); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="no-items">
                    <td class="colspanchange" colspan="5">No students found.</td>
                </tr>
            <?php endif; ?>
        </tbody>

        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-id column-primary sortable desc">
                    <a href="#"><span>ID</span></a>
                </th>
                <th scope="col" class="manage-column column-student_name sortable desc">
                    <a href="#"><span>Name</span></a>
                </th>
                <th scope="col" class="manage-column column-admission_no sortable desc">
                    <a href="#"><span>Admission No</span></a>
                </th>
                <th scope="col" class="manage-column column-class sortable desc">
                    <a href="#"><span>Class</span></a>
                </th>
                <th scope="col" class="manage-column column-section sortable desc">
                    <a href="#"><span>Section</span></a>
                </th>
            </tr>
        </tfoot>
    </table>
</div>
