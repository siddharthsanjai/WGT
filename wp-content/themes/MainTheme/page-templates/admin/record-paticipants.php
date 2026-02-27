<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
if ($active_tab == 'records') {
    $particiable_type = 'record';
} elseif ($active_tab == 'appreciation') {
    $particiable_type = 'appreciation';
}
echo '<div class="wrap participants-page">';
echo '<h1 class="wp-heading-inline">Participants</h1>';

// ✅ Upload form
echo '<div class="participants-toolbar">';
echo '<form id="participants-upload-form" method="post" enctype="multipart/form-data" style="display:inline-block;">';
wp_nonce_field('upload_participants_action', 'upload_participants_nonce');
echo '<label for="participants_file" class="button button-primary">+ Upload Participants</label>';
echo '<input type="file" id="participants_file" name="participants_file" accept=".csv,.xlsx" style="display:none;">';
echo '<input type="hidden" name="upload_participants" value="1">';
echo '<input type="hidden" name="upload_participants_type" value="' . esc_attr($particiable_type) . '">';
echo '</form>';
echo '</div>';

// ✅ Handle upload
if (
    isset($_POST['upload_participants'], $_FILES['participants_file']['tmp_name']) &&
    check_admin_referer('upload_participants_action', 'upload_participants_nonce') && 
    ! empty($_POST['upload_participants_type'])
) {
    require_once get_template_directory() . '/vendor/autoload.php'; // PhpSpreadsheet

    $file = $_FILES['participants_file']['tmp_name'];
    $module = $_POST['upload_participants_type'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray();

        global $wpdb;
        $table = $wpdb->prefix . "participants";

        // ✅ Get current record id from URL (?id=xxxx)
        $particiable_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : null;

        // ✅ India time
        $dt  = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $now = $dt->format('Y-m-d H:i:s');

        foreach ($rows as $i => $row) {
            if ($i === 0) continue; // skip header row

            $sno  = intval($row[0]);   // just reference, not stored
            $name = sanitize_text_field($row[1]);

            if (!$name || !$particiable_id) continue;

            $insert = $wpdb->insert(
                $table,
                [
                    'id'               => wp_generate_uuid4(),
                    'particiable_type' => $module,
                    'particiable_id'   => $particiable_id, // ✅ linked to page record
                    'name'             => $name,
                    'created_at'       => $now,
                    'updated_at'       => $now
                ],
                ['%s','%s','%s','%s','%s']
            );
            if ($insert === false) {
                throw new Exception('Database insert failed at row ' . ($i + 1));
            }
        }

        echo '<div class="notice notice-success is-dismissible"><p>✅ Participants imported successfully.</p></div>';

    } catch (Exception $e) {
        echo '<div class="notice notice-error"><p>❌ Error importing file: ' . esc_html($e->getMessage()) . '</p></div>';
    }
}

// ✅ Table
$participantsTable = new Records_Participants_List_Table($active_tab);
$participantsTable->prepare_items();

// ✅ Custom pagination text
$total    = $participantsTable->get_pagination_arg('total_items');
$per_page = $participantsTable->get_pagination_arg('per_page');
$page     = $participantsTable->get_pagination_arg('page');
$start    = (($page - 1) * $per_page) + 1;
$end      = min($start + $per_page - 1, $total);

echo '<div class="participants-meta">';
printf('<span>Showing %d-%d of %d results</span>', $start, $end, $total);
echo '</div>';

// ✅ Search + Table
echo '<form method="post">';
$participantsTable->search_box('Search participants', 'participant_search');
$participantsTable->display();
echo '</form>';

echo '</div>';
