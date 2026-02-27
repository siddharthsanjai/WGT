<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class IBR_Payments_List_Table extends WP_List_Table
{

    function get_columns()
    {
        return [
            'sno'           => __('S.No', 'wgt'),
            'paid_at'       => __('Payment Date', 'wgt'),
            'application_id' => __('Application ID', 'wgt'),
            'receipt_id'    => __('Receipt No', 'wgt'),
            'id'  => __('Reference ID', 'wgt'),
            'gateway'  => __('Payment Type', 'wgt'),
            'user_reg_id'   => __('User Reg ID', 'wgt'),
            'user_email'    => __('User Email', 'wgt'),
            'gateway'       => __('Gateway', 'wgt'),
            'country'       => __('Country', 'wgt'),
            'amount'        => __('Amount', 'wgt'),
            'status'        => __('Status', 'wgt'),
            'actions'       => __('Actions', 'wgt'),
        ];
    }

    function prepare_items()
    {
        global $wpdb;

        $per_page     = 10;
        $current_page = $this->get_pagenum();
        $search       = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $payments = $wpdb->prefix . 'payments';

        $apt_women           = $wpdb->prefix . 'apt_women';
        $inspiring_humans    = $wpdb->prefix . 'inspiring_humans';
        $records             = $wpdb->prefix . 'records';
        $appreciation        = $wpdb->prefix . 'appreciation';
        $super_talented_kids = $wpdb->prefix . 'super_talented_kids';

        /* ---------------------------------
        * INNER QUERY (build application_id)
        * --------------------------------- */
        $inner_sql = "
        SELECT 
            p.*,
            COALESCE(
                aw.application_id,
                ih.application_id,
                r.application_id,
                ap.application_id,
                stk.application_id
            ) AS application_id
        FROM $payments p

        LEFT JOIN $apt_women aw
            ON p.payable_type = 'apt-women' AND p.payable_id = aw.id

        LEFT JOIN $inspiring_humans ih
            ON p.payable_type = 'inspiring-human' AND p.payable_id = ih.id

        LEFT JOIN $records r
            ON p.payable_type = 'record' AND p.payable_id = r.id

        LEFT JOIN $appreciation ap
            ON p.payable_type = 'appreciation' AND p.payable_id = ap.id

        LEFT JOIN $super_talented_kids stk
            ON p.payable_type = 'super-talented-kid' AND p.payable_id = stk.id

        WHERE p.status = 'Paid'
    ";

        /* ---------------------------------
     * OUTER WHERE (search safely)
     * --------------------------------- */
        $outer_where = '';
        $params      = [];

        if ($search !== '') {
            $outer_where = "
            WHERE (
                payment_data.id LIKE %s
                OR payment_data.receipt_id LIKE %s
                OR payment_data.application_id LIKE %s
            )
        ";

            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        /* ---------------------------------
     * COUNT QUERY
     * --------------------------------- */
        $count_sql = "
        SELECT COUNT(*)
        FROM (
            $inner_sql
        ) AS payment_data
        $outer_where
    ";

        $total_items = $wpdb->get_var(
            $wpdb->prepare($count_sql, ...$params)
        );

        /* ---------------------------------
     * DATA QUERY
     * --------------------------------- */
        $offset = ($current_page - 1) * $per_page;

        $data_sql = "
        SELECT *
        FROM (
            $inner_sql
        ) AS payment_data
        $outer_where
        ORDER BY payment_data.paid_at DESC
        LIMIT %d OFFSET %d
    ";

        $data_params = array_merge($params, [$per_page, $offset]);

        // Debug if needed
        // echo $wpdb->prepare($data_sql, ...$data_params);

        $this->items = $wpdb->get_results(
            $wpdb->prepare($data_sql, ...$data_params),
            ARRAY_A
        );

        /* ---------------------------------
     * Pagination
     * --------------------------------- */
        $this->_column_headers = [$this->get_columns(), [], []];

        $this->set_pagination_args([
            'total_items' => (int) $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    function column_default($item, $column_name)
    {
        global $new_records_date, $wpdb;
        $created_at = strtotime($item['created_at']);
        $cutoff = strtotime($new_records_date);
        $payable_type = ($item['payable_type'] == 'appreciation') ? str_replace('-', '_', $item['payable_type']) : str_replace('-', '_', $item['payable_type']) . 's';
        $user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}{$payable_type} WHERE id = %s", $item['payable_id']));
        if ($created_at >= $cutoff) {
            $user = get_userdata($user_id);
            $user_email = $user->user_email;
            $registration_id = get_user_meta($user_id, 'registration_id', true);
        } else {
            $user_email = $wpdb->get_var($wpdb->prepare("SELECT email FROM users WHERE id = %d", $user_id));
            $registration_id = $wpdb->get_var($wpdb->prepare("SELECT registration_id FROM users WHERE id = %d", $user_id));
        }
        switch ($column_name) {
            case 'sno':
                $current_page = $this->get_pagenum();
                $per_page = $this->get_pagination_arg('per_page');
                return ($current_page - 1) * $per_page + array_search($item, $this->items) + 1;

            case 'paid_at':
                return esc_html($item['paid_at']);
            case 'application_id':
                $payments = new IBR_Payments();
                $payment = $payments->get_payment($item['id']);
                return isset($payment->application_id) ? esc_html($payment->application_id) : '';
            case 'receipt_id':
                return esc_html($item['receipt_id']);
            case 'id':
                return esc_html($item['id']);
            case 'gateway':
                return esc_html($item['gateway']);
            case 'user_reg_id':
                return esc_html($registration_id);
            case 'user_email':
                return $user_email;
            case 'country':
                return esc_html($item['country']);
            case 'amount':
                return esc_html($item['currency'] . ' ' . $item['amount']);
            case 'status':
                return esc_html($item['status']);

            case 'actions':
                $invoice_url = admin_url("?page=your_page&tab=payments&action=view_invoice&id=" . urlencode($item['id']));
                $send_url = admin_url("?page=your_page&tab=payments&action=send_invoice&id=" . urlencode($item['id']));
                return '<a href="' . $invoice_url . '">View Invoice</a> | <a href="' . $send_url . '">Send Invoice</a>';

            default:
                return '';
        }
    }
}
