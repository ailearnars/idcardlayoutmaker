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
    echo '<div class="saas-notice saas-notice-success"><p>Student deleted successfully.</p></div>';
}

$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$limit = 50;
$offset = ($paged - 1) * $limit;

$args = array(
    'limit'  => $limit,
    'offset' => $offset
);

$current_school_filter = get_user_meta(get_current_user_id(), 'school_id_card_active_school', true);
if ($current_school_filter > 0) {
    $args['school_id'] = $current_school_filter;
}

$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
if (!empty($search_query)) {
    $args['search'] = $search_query;
}

$class_filter = isset($_GET['class_filter']) ? sanitize_text_field($_GET['class_filter']) : '';
if (!empty($class_filter)) {
    $args['class'] = $class_filter;
}

$students = school_id_card_maker_get_students($args);
$total_students = school_id_card_maker_get_students_count($args);

// Get unique classes for the dropdown filter based on the current school
$unique_classes = school_id_card_maker_get_unique_classes($current_school_filter);
$total_pages = ceil($total_students / $limit);
?>

<div class="wrap saas-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--saas-border); padding-bottom: 16px; margin-bottom: 24px;">
        <h1 style="border: none; margin: 0; padding: 0;">Student List</h1>
        <div style="display: flex; gap: 16px; align-items: center;">
            <form id="student-list-filter-form" method="get" action="" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="page" value="school-id-card-maker">

                <input type="search" id="live-search-input" name="s" value="<?php echo esc_attr($search_query); ?>" class="saas-input" placeholder="Search by Name or Roll No..." style="width: 250px;">

                <select name="class_filter" class="saas-select" style="width: auto;" onchange="document.getElementById('student-list-filter-form').submit();">
                    <option value="">All Classes</option>
                    <?php foreach ($unique_classes as $cls) : ?>
                        <?php if(!empty($cls)): ?>
                        <option value="<?php echo esc_attr($cls); ?>" <?php selected($class_filter, $cls); ?>><?php echo esc_html($cls); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="saas-btn saas-btn-secondary" style="display:none;">Filter</button>
                <?php if (!empty($search_query) || !empty($class_filter)): ?>
                    <a href="?page=school-id-card-maker" class="saas-btn saas-btn-secondary" style="color: var(--saas-danger) !important; border-color: transparent;">Clear</a>
                <?php endif; ?>
            </form>

            <a href="?page=school-id-card-maker-add" class="saas-btn saas-btn-primary">Add New Student</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let timer;
            const searchInput = document.getElementById('live-search-input');
            const filterForm = document.getElementById('student-list-filter-form');

            // Move cursor to end of input if already has text
            if(searchInput.value.length > 0) {
                searchInput.focus();
                let val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }

            searchInput.addEventListener('keyup', function(e) {
                clearTimeout(timer);
                // Don't trigger on functional keys
                if(e.keyCode === 9 || e.keyCode === 16 || e.keyCode === 17 || e.keyCode === 18) return;

                timer = setTimeout(() => {
                    filterForm.submit();
                }, 600); // Wait 600ms after user stops typing
            });
        });
    </script>

    <div class="saas-table-container">
        <table class="saas-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Admission No</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)) : ?>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td><?php echo esc_html($student->id); ?></td>
                            <td>
                                <strong><a href="?page=school-id-card-maker-add&id=<?php echo esc_attr($student->id); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html($student->student_name); ?></a></strong>
                            </td>
                            <td><?php echo esc_html($student->admission_no); ?></td>
                            <td><?php echo esc_html($student->class); ?></td>
                            <td><?php echo esc_html($student->section); ?></td>
                            <td>
                                <div class="saas-actions">
                                    <a href="?page=school-id-card-maker-add&id=<?php echo esc_attr($student->id); ?>" class="saas-action-edit">Edit</a>
                                    <a href="<?php echo wp_nonce_url("?page=school-id-card-maker&action=delete&id={$student->id}", 'delete_student'); ?>" class="saas-action-delete" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                                    <a href="?page=school-id-card-maker-generate&action=single&student_id=<?php echo esc_attr($student->id); ?>" class="saas-action-generate">Generate</a>
                                    <form method="post" action="?page=school-id-card-maker-generate" style="display:inline; margin:0; padding:0;">
                                        <?php wp_nonce_field('generate_cards', 'school_id_card_maker_generate_nonce'); ?>
                                        <input type="hidden" name="student_id" value="<?php echo esc_attr($student->id); ?>">
                                        <input type="hidden" name="orientation" value="horizontal">
                                        <input type="hidden" name="template" value="template-1">
                                        <input type="hidden" name="format" value="print">
                                        <input type="hidden" name="generate_id_cards" value="1">
                                        <button type="submit" style="background:none; border:none; padding:0; font-size: 13px; font-weight: 500; font-family: inherit; color:#6B7280; cursor:pointer;">Print</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 32px; color: var(--saas-text-muted);">No students found. Add your first student to get started.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 0) : ?>
        <div class="saas-pagination">
            <div class="saas-pagination-info">
                Showing <strong><?php echo esc_html($offset + 1); ?></strong> to <strong><?php echo esc_html(min($offset + $limit, $total_students)); ?></strong> of <strong><?php echo esc_html($total_students); ?></strong> items
            </div>
            <?php if ($total_pages > 1) : ?>
                <div class="saas-pagination-links">
                    <?php
                    echo paginate_links(array(
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total'     => $total_pages,
                        'current'   => $paged,
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
