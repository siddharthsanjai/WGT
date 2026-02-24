<?php
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Records_Participants_List_Table extends WP_List_Table
{
    private $table_name;
    private $module;

    public function __construct($module = 'records')
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'participants';
        $this->module     = $module;

        parent::__construct([
            'singular' => 'participant',
            'plural'   => 'participants',
            'ajax'     => true, // Enable AJAX for inline edit
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'             => '<input type="checkbox" />',
            'sno'            => 'S.No',
            'application_id' => 'Record Application ID',
            'name'           => 'Participant Name',
            'action'         => 'Action',
        ];
    }

    public function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
        ];
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            esc_attr($item['id'])
        );
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'application_id':
                return esc_html($item['application_id']);
            default:
                return $item[$column_name] ?? '';
        }
    }

    protected function column_sno($item)
    {
        return $item['sno'];
    }

    protected function column_name($item)
    {
        return sprintf(
            '<span class="participant-name">%s</span>
             <input type="text" class="edit-input-participant" value="%s" style="display:none;width:200px;">
             <a href="#" class="edit-btn-participant dashicons dashicons-edit" data-id="%s" title="Edit"></a>
             <button class="button button-primary save-btn-participant" style="display:none;" data-id="%s">Save</button>
             <button class="button cancel-btn-participant" style="display:none;">Cancel</button>',
            esc_html($item['name']),
            esc_attr($item['name']),
            esc_attr($item['id']),
            esc_attr($item['id'])
        );
    }

    protected function column_action($item)
    {
        return sprintf(
            '<a href="#" class="delete-btn-participant dashicons dashicons-trash" data-id="%s" title="Delete"></a>',
            esc_attr($item['id'])
        );
    }

    private function get_participants($per_page = 15, $page_number = 1)
    {
        global $wpdb;

        $participants = $this->table_name;
        $modules      = $wpdb->prefix . $this->module;
        $type         = $this->module;
        if ($this->module === 'records') {
            $type = 'record';
        }

        $sql = "SELECT p.id, p.particiable_id, p.name, p.created_at, p.updated_at,
                       m.application_id
                FROM $participants p
                LEFT JOIN $modules m ON p.particiable_id = m.id";

        $where = [];

        if (! empty($_GET['id'])) {
            $record_id = sanitize_text_field($_GET['id']);
            $where[]   = $wpdb->prepare("p.particiable_id = %s AND p.particiable_type = %s", $record_id, $type);
        }

        if (! empty($_REQUEST['s'])) {
            $search = '%' . $wpdb->esc_like($_REQUEST['s']) . '%';
            $where[] = $wpdb->prepare("(p.name LIKE %s OR m.application_id LIKE %s)", $search, $search);
        }

        if (! empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.id DESC";
        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, ($page_number - 1) * $per_page);

        $results = $wpdb->get_results($sql, ARRAY_A);
        
        $sno_start = ($page_number - 1) * $per_page + 1;
        foreach ($results as $i => &$row) {
            $row['sno'] = $sno_start + $i;
            $row['application_id'] = $row['application_id'] ?: '';
        }

        return $results;
    }

    private function record_count()
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$this->table_name} p";

        $where = [];
        if (! empty($_GET['id'])) {
            $where[] = $wpdb->prepare("p.particiable_id = %s", sanitize_text_field($_GET['id']));
        }

        if (! empty($_REQUEST['s'])) {
            $search = '%' . $wpdb->esc_like($_REQUEST['s']) . '%';
            $where[] = $wpdb->prepare("p.name LIKE %s", $search);
        }

        if (! empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return (int) $wpdb->get_var($sql);
    }

    public function prepare_items()
    {
        $per_page     = 15;
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->_column_headers = [$this->get_columns(), [], []];
        $this->items = $this->get_participants($per_page, $current_page);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /** Handle bulk and single delete via AJAX */
    public static function ajax_delete()
    {
        check_ajax_referer('participants_nonce', '_ajax_nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'participants';

        if (!empty($_POST['ids'])) {
            $ids = array_map('sanitize_text_field', $_POST['ids']);
            $placeholders = implode(',', array_fill(0, count($ids), '%s'));
            $query_args = array_merge(["DELETE FROM $table WHERE id IN ($placeholders)"], $ids);
            $wpdb->query(call_user_func_array([$wpdb, 'prepare'], $query_args));
        }

        wp_send_json_success();
    }
}

// Register AJAX handler
