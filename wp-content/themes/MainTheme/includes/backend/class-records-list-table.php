<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Records_List_Table extends WP_List_Table
{
    private $cutoff_date;

    public function __construct()
    {
        global $new_records_date;
        parent::__construct([
            'singular' => 'record',
            'plural'   => 'records',
            'ajax'     => false,
        ]);
        $this->cutoff_date = $new_records_date;
    }

    public function get_columns()
    {
        return [
            'sno'             => 'S.No.',
            'seen'            => 'Seen',
            'date'            => 'Date',
            'time'            => 'Time',
            'application_id'  => 'Application ID',
            'name'            => 'Name',
            'email'           => 'Email',
            'mobile'          => 'Mobile No.',
            'category_fee_id' => 'Category',
            'participants'    => 'Participants',
            'upload_participants'    => 'Upload Participants',
            // 'title'           => 'Title',
            'country'         => 'Country',
            // 'state'           => 'State',
            // 'city'            => 'City',
            // 'lead_owner'      => 'Lead Owner',
            'status'          => 'Lead Status',
            // 'lead_stage'      => 'Lead Stage',
            'approval_status' => 'Approval Status',
            'email_triggers'  => 'Email triggers',
            'payment_status'  => 'Payment',
            // 'remark'          => 'Lead remark',
            'action'          => 'Action',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            // 'application_id' => ['application_id', false],
            'date'           => ['created_at', false],
        ];
    }

    public function prepare_items()
    {
        global $wpdb;

        $per_page     = 10;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $orderby = isset($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'r.created_at';
        $order   = (isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc'])) ? strtoupper($_GET['order']) : 'DESC';

        // Sanitize filters
        $search       = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status       = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $category     = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $country      = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
        $state        = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
        $approval     = isset($_GET['approval']) ? trim($_GET['approval']) : '';
        $attempt_from = isset($_GET['attempt_from']) ? sanitize_text_field($_GET['attempt_from']) : '';
        $attempt_to   = isset($_GET['attempt_to']) ? sanitize_text_field($_GET['attempt_to']) : '';
        $created_from = isset($_GET['created_from']) ? sanitize_text_field($_GET['created_from']) : '';
        $created_to   = isset($_GET['created_to']) ? sanitize_text_field($_GET['created_to']) : '';

        // Build WHERE clause
        $where_clauses = [];
        $params = [];

        if ($search) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(" .
                "r.application_id LIKE %s OR " .
                "r.mobile LIKE %s OR " .
                "r.user_id LIKE %s OR " .
                "r.title LIKE %s OR " .
                "r.user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_email LIKE %s) OR " .
                "r.user_id IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'first_name' AND meta_value LIKE %s) OR " .
                "r.user_id IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'last_name' AND meta_value LIKE %s) OR " .
                "r.user_id IN (SELECT id FROM users WHERE name LIKE %s) OR " .
                "r.user_id IN (SELECT id FROM users WHERE email LIKE %s) OR " .
                "r.id IN (SELECT holdable_id FROM {$wpdb->prefix}holders WHERE name LIKE %s AND `index`='0') " .
                ")";
            array_push($params, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like);
        }

        if ($status) {
            $where_clauses[] = "r.status = %s";
            $params[] = $status;
        }

        if ($country) {
            $where_clauses[] = "r.country = %s";
            $params[] = ibr_get_country_name_by_code($country) ?: $country;
        }

        if ($state) {
            $where_clauses[] = "r.state = %s";
            $params[] = $state;
        }

        if ($approval !== '') {
            $where_clauses[] = "r.approval_status = %s";
            $params[] = $approval;
        }

        if ($attempt_from) {
            $where_clauses[] = "r.date >= %s";
            $params[] = $attempt_from;
        }

        if ($attempt_to) {
            $where_clauses[] = "r.date <= %s";
            $params[] = $attempt_to;
        }

        if ($created_from) {
            $where_clauses[] = "r.created_at >= %s";
            $params[] = $created_from;
        }

        if ($created_to) {
            $where_clauses[] = "r.created_at <= %s";
            $params[] = $created_to;
        }

        if ($category) {
            $category_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}categories WHERE module = %s AND `index` = %d",
                'records',
                $category
            ));

            if ($category_id) {
                $category_fee_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}category_fees WHERE category_id = %d",
                    $category_id
                ));

                if (!empty($category_fee_ids)) {
                    $placeholders = implode(',', array_fill(0, count($category_fee_ids), '%d'));
                    $where_clauses[] = "r.category_fee_id IN ($placeholders)";
                    $params = array_merge($params, $category_fee_ids);
                } else {
                    $where_clauses[] = "1 = 0"; // no fees
                }
            } else {
                $where_clauses[] = "1 = 0"; // no category
            }
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        // Count distinct records
        $count_sql = "SELECT COUNT(DISTINCT r.id) 
                  FROM {$wpdb->prefix}records r 
                  $where_sql";
        $total_items = !empty($params)
            ? $wpdb->get_var($wpdb->prepare($count_sql, ...$params))
            : $wpdb->get_var($count_sql);

        // Get data with single payment_status per record
        $data_sql = "
        SELECT r.*,
               (SELECT p.status 
                FROM {$wpdb->prefix}payments p 
                WHERE p.payable_id = r.id 
                ORDER BY p.id ASC 
                LIMIT 1) AS payment_status
        FROM {$wpdb->prefix}records r
        $where_sql
        ORDER BY $orderby $order
        LIMIT %d OFFSET %d
        ";
        $data_params = array_merge($params, [$per_page, $offset]);
        $results = $wpdb->get_results($wpdb->prepare($data_sql, ...$data_params), ARRAY_A);
        // Setup columns & pagination
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        $this->items = $results;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    public function column_default($item, $column_name)
    {
        global $common_class, $wpdb;
        switch ($column_name) {
            case 'date':
                return date('d-M-Y', strtotime($item['created_at']));

            case 'time':
                return date('H:i', strtotime($item['created_at']));

            case 'state':
                $country = get_country_code_by_name($item['country']);
                return get_state_by_state_code($country, $item['state']);

            case 'status':
                if ($item['status'] === 'Cancelled') {
                    return '<span class="text-danger">' . esc_html($item['status']) . '</span>';
                }
                return '<select class="update_record" data-id="' . esc_attr($item['id']) . '" data-module="records" data-action_type="status">
                    <option ' . ($item['status'] == 'Active' ? 'selected' : '') . '>Active</option>
                    <option ' . ($item['status'] == 'Inactive' ? 'selected' : '') . '>Inactive</option>
                </select>';

            case 'lead_stage':
                $html = '<select class="update_record active_disable" data-id="' . esc_attr($item['id']) . '" data-module="records" data-action_type="lead_stage" ' . ($item['status'] === 'Inactive' ? 'disabled' : '') . '>';
                foreach (ibr_get_lead_stages() as $stage) {
                    $selected = ($item['lead_stage'] === $stage) ? 'selected' : '';
                    $html .= '<option value="' . esc_attr($stage) . '" ' . $selected . '>' . esc_html($stage) . '</option>';
                }
                $html .= '</select>';
                return $html;

            case 'approval_status':
                $html = '<select class="update_record active_disable" data-id="' . esc_attr($item['id']) . '" data-module="records" data-action_type="approval_status" ' . ($item['status'] === 'Inactive' ? 'disabled' : '') . '>';
                foreach ($common_class->get_all_application_status() as $status) {
                    $selected = ($item['approval_status'] === $status) ? 'selected' : '';
                    $html .= '<option value="' . esc_attr($status) . '" ' . $selected . '>' . esc_html($status) . '</option>';
                }
                $html .= '</select>';
                return $html;

            case 'email_triggers':
                $conditions = $common_class->email_trigger_conditions($item['id'], 'records');
                $email_types = $common_class->get_email_types();
                $html = '<select class="update_record active_disable" data-id="' . esc_attr($item['id']) . '" data-module="records" data-action_type="email_triggers" ' . (($conditions) ? 'disabled' : '') . '>';
                $html .= '<option value="">Trigger Email</option>';
                foreach ($email_types as $key => $type) {
                    $selected = ($item['email_triggers'] === $key) ? 'selected' : '';
                    $html .= '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($type) . '</option>';
                }
                $html .= '</select>';
                return $html;

            case 'payment_status':
                return esc_html($item['payment_status']);

            case 'remark':
                return '<input type="text" class="update_record" data-id="' . esc_attr($item['id']) . '" value="' . esc_attr($item['remark']) . '" data-module="records" data-action_type="remark"/>';

            case 'lead_owner':
                return '<input type="text" class="update_record" data-id="' . esc_attr($item['id']) . '" value="' . esc_attr($item['lead_owner']) . '" data-module="records" data-action_type="lead_owner"/>';

            case 'category_fee_id':
                $category = ibr_get_category_by_fee_id($item['category_fee_id']);
                // Assuming a function get_category_name exists to fetch category name by ID
                return esc_html($category ? $category->name : 'Unknown');

            case 'action':
                $record_id = $item['id'];
                return '
                <a class="button button-primary" href="' . 'admin.php?page=ibr&tab=records&section=basicdetails&id=' . esc_attr($record_id) . '">Edit</a<br>
                <a class="button button-primary" href="admin.php?page=ibr&tab=records&action=participants&id=' . esc_attr($record_id) . '">Participants</>
            ';

            case 'participants':
                return ($item['estimate_participants'] > 0) ? esc_html($item['estimate_participants']) : 'NA';

            case 'upload_participants':
                return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}participants WHERE particiable_id = %s", $item['id'])) ?: 'NA';
                
            case 'name':
                $name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}holders WHERE holdable_id = %s AND `index` = '0'", $item['id']));
                return esc_html($name ?: 'NA');
            case 'email':
                $created_at = strtotime($item['created_at']);
                $cutoff = strtotime($this->cutoff_date);
                $user_id = intval($item['user_id']);
                global $wpdb;
                if ($created_at >= $cutoff) {
                    $user = get_userdata($user_id);
                    if (!$user) {
                        return esc_html($item[$column_name] ?? '');
                    }
                    if ($column_name === 'email') {
                        $value = $user->user_email;
                    }
                    if ($column_name === 'name') {
                        $value = $user->first_name . ' ' . $user->last_name;
                    }
                    return $value;
                } else {
                    $db_column = $column_name;
                    $value = $wpdb->get_var($wpdb->prepare("SELECT $db_column FROM users WHERE id = %d", $user_id));
                    return esc_html($value ?? '');
                }

            default:
                return esc_html($item[$column_name] ?? '');
        }
    }


    // Optional: No checkbox column
    public function get_bulk_actions()
    {
        return [];
    }

    public function single_row($item)
    {
        $row_class = '';

        // Your condition here
        $created_at = strtotime($item['created_at']);
        $cutoff = strtotime($this->cutoff_date);
        // if ($created_at < $cutoff) {
        //     $row_class = 'row-disabled';
        // }

        if ($item['seen'] == 0) {
            $row_class = 'row-unseen';
        }

        echo '<tr class="' . esc_attr($row_class) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    public function process_bulk_action()
    {
        global $wpdb;
        $table = $this->table_name;

        // Bulk delete
        if (('delete' === $_POST['action'] || 'delete' === $_POST['action2']) && !empty($_POST['bulk-delete'])) {

            // Sanitize all IDs as strings
            $ids = array_map('sanitize_text_field', $_POST['bulk-delete']);
            if (!empty($ids)) {
                // Build placeholders for each ID
                $placeholders = implode(',', array_fill(0, count($ids), '%s'));
                $wpdb->query(
                    $wpdb->prepare("DELETE FROM $table WHERE id IN ($placeholders)", $ids)
                );
            }

            // Redirect to the same page to refresh table
            wp_redirect(remove_query_arg(['action', 'bulk-delete']));
            exit;
        }
    }

    public function column_sno($item)
    {
        static $sno = 0;

        $per_page     = 10;
        $current_page = $this->get_pagenum();

        // Calculate serial start number based on page
        $start_from = ($current_page - 1) * $per_page;

        $sno++;

        // Return serial in ascending order (1, 2, 3, 4...)
        return $start_from + $sno;
    }
}
add_action('wp_ajax_delete_participants', ['Records_Participants_List_Table', 'ajax_delete']);
