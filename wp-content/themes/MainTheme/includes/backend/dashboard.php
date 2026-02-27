<?php

add_action('wp_ajax_get_dashboard_stats', 'wgt_get_dashboard_stats');
add_action('wp_ajax_nopriv_get_dashboard_stats', 'wgt_get_dashboard_stats');

function wgt_get_dashboard_stats()
{
    check_ajax_referer('dashboard_stats_nonce', 'security');
    date_default_timezone_set('Asia/Kolkata');

    global $wpdb;

    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'today';
    $where = "1=1";
    $where1 = "1=1";
    $where2 = "1=1";

    // Week range (Mon–Sun IST)
    // WP local time
    $now_ts = current_time('timestamp', true); // UTC timestamp
    $now = wp_date('Y-m-d H:i:s', $now_ts);

    // Today range
    $today_start = wp_date('Y-m-d 00:00:00', $now_ts);
    $today_end = wp_date('Y-m-d 23:59:59', $now_ts);

    $week_start = wp_date('Y-m-d 00:00:00', strtotime('monday this week', $now_ts));
    $week_end = wp_date('Y-m-d 23:59:59', strtotime('sunday this week', $now_ts));

    if ($type === 'today') {
        $today = date('Y-m-d', $now_ts);
        $where .= $wpdb->prepare(" AND DATE(created_at) BETWEEN %s AND %s", $today_start, $today_end);
        $where2 .= $wpdb->prepare(" AND DATE(approved_at) BETWEEN %s AND %s", $today_start, $today_end);
        $where1 .= $wpdb->prepare(" AND DATE(p.paid_at) BETWEEN %s AND %s", $today_start, $today_end);
        $user_where = $wpdb->prepare(" AND DATE(user_registered) BETWEEN %s AND %s", $today_start, $today_end);
    } elseif ($type === 'week') {
        $where .= $wpdb->prepare(" AND created_at BETWEEN %s AND %s", $week_start, $week_end);
        $where2 .= $wpdb->prepare(" AND approved_at BETWEEN %s AND %s", $week_start, $week_end);
        $where1 .= $wpdb->prepare(" AND p.paid_at BETWEEN %s AND %s", $week_start, $week_end);
        $user_where = $wpdb->prepare(" AND user_registered BETWEEN %s AND %s", $week_start, $week_end);
    } elseif ($type === 'month') {
        $month = wp_date('m', $now_ts);
        $year = wp_date('Y', $now_ts);

        $where .= $wpdb->prepare(" AND MONTH(created_at) = %d AND YEAR(created_at) = %d", $month, $year);
        $where2 .= $wpdb->prepare(" AND MONTH(approved_at) = %d AND YEAR(created_at) = %d", $month, $year);
        $where1 .= $wpdb->prepare(" AND MONTH(p.paid_at) = %d AND YEAR(p.paid_at) = %d", $month, $year);
        $user_where = $wpdb->prepare(" AND MONTH(user_registered) = %d AND YEAR(user_registered) = %d", $month, $year);
    } elseif ($type === 'year') {
        $year = wp_date('Y', $now_ts);

        $where .= $wpdb->prepare(" AND YEAR(created_at) = %d", $year);
        $where2 .= $wpdb->prepare(" AND YEAR(approved_at) = %d", $year);
        $where1 .= $wpdb->prepare(" AND YEAR(p.paid_at) = %d", $year);
        $user_where = $wpdb->prepare(" AND YEAR(user_registered) = %d", $year);
    } elseif ($type === 'custom' && !empty($_POST['start_date']) && !empty($_POST['end_date'])) {

        $start_date = sanitize_text_field($_POST['start_date']) . ' 00:00:00';
        $end_date = sanitize_text_field($_POST['end_date']) . ' 23:59:59';

        $where .= $wpdb->prepare(" AND created_at BETWEEN %s AND %s", $start_date, $end_date);
        $where2 .= $wpdb->prepare(" AND approved_at BETWEEN %s AND %s", $start_date, $end_date);
        $where1 .= $wpdb->prepare(" AND p.paid_at BETWEEN %s AND %s", $start_date, $end_date);
        $user_where = $wpdb->prepare(" AND user_registered BETWEEN %s AND %s", $start_date, $end_date);
    } else {
        $user_where = "1=1";
    }

    $stats = [];

    // ✅ Only count users with "customer" role
    $stats['users'] = $wpdb->get_var("
        SELECT COUNT(DISTINCT u.ID)
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->usermeta} um 
            ON u.ID = um.user_id
        WHERE um.meta_key = '{$wpdb->prefix}capabilities'
        AND um.meta_value LIKE '%\"customer\"%'
        $user_where
    ");

    // World Records
    $stats['world_records'] = [
        'applied' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}records WHERE $where"),
        'approved' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}records WHERE $where2 AND approval_status = 'Approved'")
    ];

    // Super Talented Kids
    // $stats['stk_records'] = [
    //     'applied' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}super_talented_kids WHERE $where"),
    //     'approved' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}super_talented_kids WHERE $where2 AND approval_status = 'Approved'")
    // ];

    // Inspiring Humans
    // $stats['ih_records'] = [
    //     'applied' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}inspiring_humans WHERE $where"),
    //     'approved' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}inspiring_humans WHERE $where2 AND approval_status = 'Approved'")
    // ];

    // Apt Women
    // $stats['apt_women'] = [
    //     'applied' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}apt_women WHERE $where"),
    //     'approved' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}apt_women WHERE $where2 AND approval_status = 'Approved'")
    // ];

    // Appreciation Award
    $stats['appreciation_award'] = [
        'applied' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}appreciation WHERE $where"),
        'approved' => $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}appreciation WHERE $where2 AND approval_status = 'Approved'")
    ];
    // Revenue
    $stats['world_records_revenue'] = $wpdb->get_var("SELECT COALESCE(SUM(IF(p.currency = 'INR', p.amount, p.amount / c.value)), 0) AS revenue_in_inr FROM {$wpdb->prefix}payments p LEFT JOIN {$wpdb->prefix}conversions c ON c.from = 'INR' AND c.to = p.currency WHERE p.payable_type = 'record' AND p.status = 'Paid' AND $where1");
    // $stats['stk_records_revenue'] = $wpdb->get_var("SELECT COALESCE(SUM(IF(p.currency = 'INR', p.amount, p.amount / c.value)), 0) AS revenue_in_inr FROM {$wpdb->prefix}payments p LEFT JOIN {$wpdb->prefix}conversions c ON c.from = 'INR' AND c.to = p.currency WHERE p.payable_type = 'super-talented-kid' AND p.status = 'Paid' AND $where1");
    // $stats['ih_records_revenue'] = $wpdb->get_var("SELECT COALESCE(SUM(IF(p.currency = 'INR', p.amount, p.amount / c.value)), 0) AS revenue_in_inr FROM {$wpdb->prefix}payments p LEFT JOIN {$wpdb->prefix}conversions c ON c.from = 'INR' AND c.to = p.currency WHERE p.payable_type = 'inspiring-human' AND p.status = 'Paid' AND $where1");
    // $stats['apt_women_revenue'] = $wpdb->get_var("SELECT COALESCE(SUM(IF(p.currency = 'INR', p.amount, p.amount / c.value)), 0) AS revenue_in_inr FROM {$wpdb->prefix}payments p LEFT JOIN {$wpdb->prefix}conversions c ON c.from = 'INR' AND c.to = p.currency WHERE p.payable_type = 'apt-woman' AND p.status = 'Paid' AND $where1");
    $stats['appreciation_award_revenue'] = $wpdb->get_var("SELECT COALESCE(SUM(IF(p.currency = 'INR', p.amount, p.amount / c.value)), 0) AS revenue_in_inr FROM {$wpdb->prefix}payments p LEFT JOIN {$wpdb->prefix}conversions c ON c.from = 'INR' AND c.to = p.currency WHERE p.payable_type = 'appreciation' AND p.status = 'Paid' AND $where1");

    $html = '
    <div class="stat-box"><h3>Users</h3><p class="stat-number">' . $stats['users'] . '</p></div>
    <div class="stat-box"><h3>World Records</h3><p class="stat-number">' . $stats['world_records']['applied'] . '/' . $stats['world_records']['approved'] . '</p></div>
    
    

    <div class="stat-box"><h3>Appreciation Awards</h3><p class="stat-number">' . $stats['appreciation_award']['applied'] . '/' . $stats['appreciation_award']['approved'] . '</p></div>
    <div class="stat-box"><h3>World Records Revenue</h3><p class="stat-number">INR ' . round($stats['world_records_revenue'], 2) . '</p></div>
    
  
    <div class="stat-box"><h3>Appreciation Awards Revenue</h3><p class="stat-number">INR ' . round($stats['appreciation_award_revenue'], 2) . '</p></div>';

    wp_send_json_success(['html' => $html]);
}
