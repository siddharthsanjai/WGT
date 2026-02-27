<?php
$GLOBALS['new_records_date'] = '2025-10-08';
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// AJAX handler for dashboard data
function wgt_get_dashboard_data()
{
    check_ajax_referer('wgt-nonce', 'nonce');

    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'today';

    // Get user count
    $user_count = count_users();
    $total_users = $user_count['total_users'];

    // Get revenue data (this is a placeholder - you'll need to implement actual revenue tracking)
    $revenue = 0;

    wp_send_json_success(array(
        'total_users' => $total_users,
        'total_revenue' => $revenue
    ));
}
add_action('wp_ajax_wgt_get_dashboard_data', 'wgt_get_dashboard_data');

/**
 * Handle role creation form submission
 */
function wgt_handle_role_creation_ajax()
{
    if (!isset($_POST['wgt_role_nonce']) || !wp_verify_nonce($_POST['wgt_role_nonce'], 'wgt_create_role')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    // Validate required fields
    $required_fields = array('name', 'email', 'mobile', 'role', 'status', 'password', 'confirm_password');
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(['message' => "Please fill in all required fields."]);
        }
    }

    if (!is_email($_POST['email'])) {
        wp_send_json_error(['message' => "Please enter a valid email address."]);
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        wp_send_json_error(['message' => "Passwords do not match."]);
    }

    if (email_exists($_POST['email'])) {
        wp_send_json_error(['message' => "Email already exists."]);
    }

    $userdata = array(
        'user_login' => sanitize_email($_POST['email']),
        'user_email' => sanitize_email($_POST['email']),
        'user_pass' => $_POST['password'],
        'display_name' => sanitize_text_field($_POST['name']),
        'role' => sanitize_text_field($_POST['role'])
    );

    $user_id = wp_insert_user($userdata);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
    }

    // Save user meta
    update_user_meta($user_id, 'phone_number', sanitize_text_field($_POST['phone_number']));
    update_user_meta($user_id, 'phone_code', sanitize_text_field($_POST['country_code']));
    update_user_meta($user_id, 'gender', sanitize_text_field($_POST['gender']));
    update_user_meta($user_id, 'status', sanitize_text_field($_POST['status']));

    // Save profile photo ID (optional)
    if (!empty($_POST['profile_photo_id'])) {
        update_user_meta($user_id, 'profile_photo', intval($_POST['profile_photo_id']));
    }

    $crm_login_link = home_url();
    // Optional: Send email
    WC()->mailer()->get_emails()['WC_Email_CRM_Role_Assigned']
        ->trigger(
            $_POST['email'],
            $_POST['name'],
            $_POST['role'],
            $crm_login_link
        );

    wp_send_json_success(['message' => 'User created successfully.']);
}

add_action('wp_ajax_wgt_create_role', 'wgt_handle_role_creation_ajax');
add_action('wp_ajax_nopriv_wgt_create_role', 'wgt_handle_role_creation_ajax');

// Add custom user meta fields
function wgt_add_user_meta_fields($user)
{
    $mobile_no = get_user_meta($user->ID, 'mobile_no', true);
    $gender = get_user_meta($user->ID, 'gender', true);
    $status = get_user_meta($user->ID, 'status', true);
    $custom_role_id = get_user_meta($user->ID, 'custom_role_id', true);
?>
    <h3>Additional Information</h3>
    <table class="form-table">
        <tr>
            <th><label for="mobile_no">Mobile Number</label></th>
            <td>
                <input type="text" name="mobile_no" id="mobile_no" value="<?php echo esc_attr($mobile_no); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="gender">Gender</label></th>
            <td>
                <select name="gender" id="gender">
                    <option value="">Select Gender</option>
                    <option value="male" <?php selected($gender, 'male'); ?>>Male</option>
                    <option value="female" <?php selected($gender, 'female'); ?>>Female</option>
                    <option value="other" <?php selected($gender, 'other'); ?>>Other</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="status">Status</label></th>
            <td>
                <select name="status" id="status">
                    <option value="active" <?php selected($status, 'active'); ?>>Active</option>
                    <option value="inactive" <?php selected($status, 'inactive'); ?>>Inactive</option>
                </select>
            </td>
        </tr>
    </table>
<?php
}
add_action('show_user_profile', 'wgt_add_user_meta_fields');
add_action('edit_user_profile', 'wgt_add_user_meta_fields');

// Save custom user meta fields
function wgt_save_user_meta_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, 'mobile_no', sanitize_text_field($_POST['mobile_no']));
    update_user_meta($user_id, 'gender', sanitize_text_field($_POST['gender']));
    update_user_meta($user_id, 'status', sanitize_text_field($_POST['status']));
}
add_action('personal_options_update', 'wgt_save_user_meta_fields');
add_action('edit_user_profile_update', 'wgt_save_user_meta_fields');

/**
 * AJAX handler for getting user data
 */
function wgt_get_user_data()
{
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wgt_get_user_data')) {
        wp_send_json_error('Invalid nonce');
    }

    // Check permissions
    if (!current_user_can('access_wgt_menu')) {
        wp_send_json_error('Insufficient permissions');
    }

    // Get user ID
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
    }

    // Get user data
    $user = get_userdata($user_id);
    if (!$user) {
        wp_send_json_error('User not found');
    }

    $profile_image = get_user_meta($user->ID, 'profile_photo', true);
    $profile_image_url = wp_get_attachment_url($profile_image);
    // Prepare user data
    $user_data = array(
        'ID' => $user->ID,
        'display_name' => $user->display_name,
        'user_email' => $user->user_email,
        'roles' => $user->roles,
        'phone_number' => get_user_meta($user->ID, 'phone_number', true),
        'phone_code' => get_user_meta($user->ID, 'phone_code', true),
        'gender' => get_user_meta($user->ID, 'gender', true),
        'status' => get_user_meta($user->ID, 'status', true),
        'profile_image_id' => $profile_image,
        'profile_image_url' => get_user_profile_image($user->ID),
        'is_admin' => in_array('administrator', $user->roles)
    );

    wp_send_json_success($user_data);
}
add_action('wp_ajax_wgt_get_user_data', 'wgt_get_user_data');

/**
 * AJAX handler for updating user data
 */

function wgt_update_user_data()
{
    // Verify nonce
    if (!isset($_POST['wgt_edit_nonce']) || !wp_verify_nonce($_POST['wgt_edit_nonce'], 'wgt_edit_user')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    if (!current_user_can('edit_users')) {
        wp_send_json_error(['message' => 'You do not have permission to update users.']);
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id || !($user = get_userdata($user_id))) {
        wp_send_json_error(['message' => 'Invalid user ID']);
    }

    if (in_array('administrator', $user->roles)) {
        wp_send_json_error(['message' => 'Cannot edit Administrator accounts']);
    }

    $name = sanitize_text_field($_POST['name']);
    // $email = sanitize_email($_POST['email']);
    $role = sanitize_text_field($_POST['role']);
    $status = sanitize_text_field($_POST['status']);
    $gender = sanitize_text_field($_POST['gender']);
    $phone = sanitize_text_field($_POST['edit_phone_number']);
    $phone_code = sanitize_text_field($_POST['edit_country_code']);

    if (!$name || !$role || !$status || !$gender || !$phone) {
        wp_send_json_error(['message' => 'All required fields must be filled']);
    }

    // if (!is_email($email)) {
    //     wp_send_json_error(['message' => 'Invalid email address']);
    // }

    $update_data = [
        'ID' => $user_id,
        'display_name' => $name,
        // 'user_email' => $email
    ];

    $updated = wp_update_user($update_data);
    if (is_wp_error($updated)) {
        wp_send_json_error(['message' => $updated->get_error_message()]);
    }
    // Update role
    if (get_role($role)) {
        foreach ($user->roles as $r) {
            $user->remove_role($r);
        }
        $user->add_role($role);
    }

    // Update meta
    update_user_meta($user_id, 'status', $status);
    update_user_meta($user_id, 'gender', $gender);
    update_user_meta($user_id, 'phone_number', $phone);
    update_user_meta($user_id, 'phone_code', $phone_code);

    // Handle profile photo ID
    if (!empty($_POST['profile_photo_id'])) {
        update_user_meta($user_id, 'profile_photo', intval($_POST['profile_photo_id']));
    }

    // Handle password update (optional)
    if (!empty($_POST['edit_password']) && $_POST['edit_password'] === $_POST['edit_confirm_password']) {
        wp_set_password($_POST['edit_password'], $user_id);
    } elseif (!empty($_POST['edit_password'])) {
        wp_send_json_error(['message' => 'Passwords do not match']);
    }

    wp_send_json_success(['message' => 'User updated successfully']);
}
add_action('wp_ajax_wgt_update_user_data', 'wgt_update_user_data');

/**
 * Handle user deletion
 */
function wgt_handle_user_deletion()
{
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['user'])) {
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_user_' . $_GET['user'])) {
            wp_die('Invalid nonce');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        $user_id = intval($_GET['user']);
        $user = get_userdata($user_id);

        // Prevent deleting admin users
        if ($user && in_array('administrator', $user->roles)) {
            wp_redirect(add_query_arg('error', 'admin_delete', admin_url('admin.php?page=wgt-admin')));
            exit;
        }

        // Prevent self-deletion
        if ($user_id === get_current_user_id()) {
            wp_redirect(add_query_arg('error', 'self_delete', admin_url('admin.php?page=wgt-admin')));
            exit;
        }

        // Delete user
        if (wp_delete_user($user_id)) {
            wp_redirect(add_query_arg('user_deleted', '1', admin_url('admin.php?page=wgt-admin')));
        } else {
            wp_redirect(add_query_arg('error', 'delete_failed', admin_url('admin.php?page=wgt-admin')));
        }
        exit;
    }
}
add_action('admin_init', 'wgt_handle_user_deletion');

add_action('wp_ajax_update_certificate_fee', 'wgt_update_certificate_fee_callback');
function wgt_update_certificate_fee_callback()
{
    check_ajax_referer('wgt-nonce', '_ajax_nonce');

    global $wpdb;
    $id = intval($_POST['id']);
    $new_fee = floatval($_POST['new_fee']);

    $table = $wpdb->prefix . "certificates"; // replace with your table name

    $updated = $wpdb->update(
        $table,
        ['fees' => $new_fee],
        ['id' => $id],
        ['%f'],
        ['%d']
    );

    if ($updated !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error("Could not update the fee.");
    }
}

add_action('wp_ajax_toggle_record_status', function () {
    check_ajax_referer('wgt-nonce');

    global $wpdb;

    $record_id = sanitize_text_field($_POST['record_id'] ?? '');
    $status    = in_array($_POST['status'], ['Active', 'Inactive']) ? $_POST['status'] : 'Inactive';

    $updated = $wpdb->update(
        'wp_records',
        ['status' => $status],
        ['id' => $record_id],
        ['%s'],
        ['%s']
    );

    if ($updated !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
});

function wgt_get_cat_fee_id($type_key, $country, $module)
{
    global $wpdb;

    $country = trim($country);
    $country = ($country === 'IN' || $country === 'India' || strtolower($country) == 'in' || strtolower($country) == 'india') ? 'India' : 'Other';

    $types = [1 => 'Solo', 2 => 'Duo', 3 => 'Group', 4 => 'Mass', 5 => 'Commercial'];

    $category_fee_id = $wpdb->get_var(
        $wpdb->prepare("
            SELECT cf.id
            FROM {$wpdb->prefix}category_fees cf
            INNER JOIN {$wpdb->prefix}categories c 
                ON cf.category_id = c.id
            WHERE c.name = %s
              AND c.module = %s
              AND cf.country = %s
            LIMIT 1
        ", $types[$type_key], $module, $country)
    );

    return $category_fee_id;
}


function wgt_get_category_by_fee_id($category_fee_id)
{
    global $wpdb;

    $category = $wpdb->get_row(
        $wpdb->prepare("
            SELECT c.*
            FROM wp_categories c
            INNER JOIN wp_category_fees cf ON c.id = cf.category_id
            WHERE cf.id = %d
            LIMIT 1
        ", $category_fee_id)
    );

    return $category; // returns full category object or null
}

function get_state_by_state_code($country_code, $state_code)
{

    $states = WC()->countries->get_states($country_code);

    $state_name = isset($states[$state_code]) ? $states[$state_code] : '';

    return $state_name;
}

function get_country_code_by_name($country_name)
{
    $countries = WC()->countries->get_countries();

    // Do a case-insensitive search
    $code = array_search(strtolower($country_name), array_map('strtolower', $countries));

    return $code ?: false; // return false if not found
}

add_action('wp_ajax_update_record_table_columns', function () {
    global $wpdb, $common_class, $wgt_emails;
    // check_ajax_referer('update_lead_owner');

    $record_id   = $_POST['record_id'];
    $lead_owner  = sanitize_text_field($_POST['lead_owner']);
    $module      = sanitize_text_field($_POST['module']);
    $action_type = sanitize_text_field($_POST['action_type'] ?? '');
    $status      = sanitize_text_field($_POST['status'] ?? '');
    $lead_stage  = sanitize_text_field($_POST['lead_stage'] ?? '');
    $approval_status = sanitize_text_field($_POST['approval_status'] ?? '');
    $email_triggers  = sanitize_text_field($_POST['email_triggers'] ?? '');
    $remark          = sanitize_text_field($_POST['remark'] ?? '');

    if (!$record_id) {
        wp_send_json_error('Invalid record ID.');
    }

    $table = $wpdb->prefix . $module; // replace with your table

    if ($action_type === 'lead_owner') {
        $arg = ['lead_owner' => $lead_owner];
    } else if ($action_type === 'status') {
        $arg = ['status' => $status];
    } else if ($action_type === 'lead_stage') {
        $arg = ['lead_stage' => $lead_stage];
    } else if ($action_type === 'approval_status') {
        $arg = ['approval_status' => $approval_status];
        if ($approval_status == 'Approved') {
            $arg['approved_at'] = current_time('mysql');
        }
    } else if ($action_type === 'email_triggers') {
        if (! empty($email_triggers)) {
            $record = $common_class->get_application_data_by_module($record_id, $module); // Your function to get single application
            $get_cat_fee_id = $record['category_fee_id'];
            $cat = wgt_get_category_by_fee_id($get_cat_fee_id);
            $pre = 'wr';
            if ($module == 'record') {
                $pre = 'wr';
            } else if ($module == 'super_talented_kids') {
                $pre = 'stk';
            } else if ($module == 'inspiring_humans') {
                $pre = 'ih';
            } else if ($module == 'apt_women') {
                $pre = 'aptw';
            } else if ($module == 'appreciation') {
                $pre = 'app';
            }
            $user = get_user_by('id', $record['user_id']);
            $user_email = $user->user_email;
            if ($email_triggers == 'accepted' && ($pre == 'wr' || $pre == 'app')) {
                $slug = strtolower($pre . '_' . $email_triggers . '_' . $cat->name);
            } else {
                $slug = strtolower($pre . '_' . $email_triggers);
            }
            $membership_id = get_user_meta($record['user_id'], 'registration_id', true);
            $amount     = $common_class->get_currency_conversion_rate($record['currency']) * $common_class->wgt_get_record_fee($record['category_fee_id'], 'INR');
            $html_data = ['membership_id' => $membership_id, 'application_id' => $record['application_id'], 'payment_link' => site_url() . '/application-fees-payment/?paymentID=' . $record['payment']['id'], 'user_name' => $user->first_name . ' ' . $user->last_name, 'site_name' => 'International Book Of Records', 'dispatch_date' => '', 'tracking_number' => '', 'courier_name' => '', 'concent_form_link' => get_template_directory_uri() . '/assets/pdf/consent-form.pdf', 'application_fee' => $record['currency'] . ' ' . $amount, 'site_url' => site_url(), 'my_account_url' => site_url() . '/my-account/', 'cancel_reason' => $record['cancel_reason'] ?? ''];
            $updated = $wgt_emails->send_by_slug($slug, $user_email, $html_data);
            if ($updated) {
                $arg = ['email_triggers' => $email_triggers];
            }
        }
    } else if ($action_type === 'remark') {
        $arg = ['remark' => $remark];
    } else {
        wp_send_json_error('Invalid action type.');
    }

    if ($arg) {
        $updated = $wpdb->update(
            $table,
            $arg,
            ['id' => $record_id],
            ['%s']
        );
    }

    if ($updated !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Database update failed.');
    }
});

function wgt_get_lead_stages()
{
    return [
        'Untouched',
        'Connected',
        'Not Connected',
        'Interested',
        'Not Interested',
        'Wrong Number',
        'Not A Lead'
    ];
}

// Update participant name via AJAX
add_action('wp_ajax_update_participant', function () {
    check_ajax_referer('wp_rest', '_ajax_nonce'); // Verify nonce

    if (empty($_POST['id']) || !isset($_POST['name'])) {
        wp_send_json_error('Invalid data');
    }

    global $wpdb;
    $id = sanitize_text_field($_POST['id']);
    $name = sanitize_text_field($_POST['name']);
    $table = $wpdb->prefix . 'participants';

    $updated = $wpdb->update($table, ['name' => strtoupper($name)], ['id' => $id]);

    if ($updated !== false) {
        wp_send_json_success('Update Successfully');
    } else {
        wp_send_json_error('Update failed');
    }
});

// Delete participant via AJAX
add_action('wp_ajax_delete_participants', function () {
    // check_ajax_referer('wp_rest', '_ajax_nonce'); // Verify nonce

    // check_ajax_referer('participants_nonce', '_ajax_nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'participants';

    if (!empty($_POST['ids'])) {
        $ids = array_map('sanitize_text_field', $_POST['ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '%s'));
        $query_args = array_merge(["DELETE FROM $table WHERE id IN ($placeholders)"], $ids);
        $wpdb->query(call_user_func_array([$wpdb, 'prepare'], $query_args));
    }

    wp_send_json_success();
});


// Add IBR Setting menu
add_action('admin_menu', 'wgt_add_settings_menu');
function wgt_add_settings_menu()
{
    add_menu_page(
        'WGT Settings',
        'WGT Settings',
        'manage_options',
        'wgt-settings',
        'wgt_settings_page_content',
        'dashicons-admin-generic',
        81
    );
}

// Register settings
add_action('admin_init', 'wgt_register_settings');
function wgt_register_settings()
{
    // Razorpay Section
    add_settings_section(
        'wgt_razorpay_section',
        'Razorpay Details',
        '__return_false',
        'wgt-settings'
    );
    register_setting('wgt_settings_group', 'wgt_razorpay_key');
    register_setting('wgt_settings_group', 'wgt_razorpay_secret');

    add_settings_field(
        'wgt_razorpay_key',
        'Razorpay Key',
        'wgt_input_field_callback',
        'wgt-settings',
        'wgt_razorpay_section',
        ['label_for' => 'wgt_razorpay_key']
    );
    add_settings_field(
        'wgt_razorpay_secret',
        'Razorpay Secret',
        'wgt_input_field_callback',
        'wgt-settings',
        'wgt_razorpay_section',
        ['label_for' => 'wgt_razorpay_secret', 'type' => 'password']
    );

    // Social Section
    add_settings_section(
        'wgt_social_section',
        'Social Links',
        '__return_false',
        'wgt-settings'
    );
    register_setting('wgt_settings_group', 'wgt_instagram_link');
    register_setting('wgt_settings_group', 'wgt_twitter_link');
    register_setting('wgt_settings_group', 'wgt_facebook_link');
    register_setting('wgt_settings_group', 'wgt_youtube_link');

    add_settings_field(
        'wgt_instagram_link',
        'Instagram URL',
        'wgt_input_field_callback',
        'wgt-settings',
        'wgt_social_section',
        ['label_for' => 'wgt_instagram_link']
    );
    add_settings_field(
        'wgt_twitter_link',
        'Twitter URL',
        'wgt_input_field_callback',
        'wgt-settings',
        'wgt_social_section',
        ['label_for' => 'wgt_twitter_link']
    );
    add_settings_field(
        'wgt_facebook_link',
        'Facebook URL',
        'wgt_input_field_callback',
        'wgt-settings',
        'wgt_social_section',
        ['label_for' => 'wgt_facebook_link']
    );
    add_settings_field(
        'wgt_youtube_link',
        'YouTube URL',
        'wgt_input_field_callback',
        'wgt-settings',
        'wgt_social_section',
        ['label_for' => 'wgt_youtube_link']
    );

    add_settings_section(
        'wgt_email_section',
        'Emails',
        '__return_false',
        'wgt-settings'
    );
    register_setting('wgt_settings_group', 'wgt_enquiry_email');
    add_settings_field(
        'wgt_enquiry_email',
        'Enquiry Email',
        'wgt_input_field_callback',
        'wgt-settings',
        'wgt_email_section',
        ['label_for' => 'wgt_enquiry_email']
    );

    // ✅ Payment Gateway Section
    add_settings_section(
        'wgt_payment_gateway_section',
        'Payment Gateways',
        '__return_false',
        'wgt-settings'
    );

    // Register two options
    register_setting('wgt_settings_group', 'wgt_india_payment_gateway');
    register_setting('wgt_settings_group', 'wgt_other_payment_gateway');

    // Add India Payment Gateway field
    add_settings_field(
        'wgt_india_payment_gateway',
        'India Payment Gateway',
        'wgt_select_field_callback',
        'wgt-settings',
        'wgt_payment_gateway_section',
        [
            'label_for' => 'wgt_india_payment_gateway',
            'options'   => [
                'razorpay' => 'Razorpay',
                'paypal'   => 'PayPal',
            ]
        ]
    );

    // Add Other Payment Gateway field
    add_settings_field(
        'wgt_other_payment_gateway',
        'Other Payment Gateway',
        'wgt_select_field_callback',
        'wgt-settings',
        'wgt_payment_gateway_section',
        [
            'label_for' => 'wgt_other_payment_gateway',
            'options'   => [
                'razorpay' => 'Razorpay',
                'paypal'   => 'PayPal',
            ]
        ]
    );
}

// Input field callback
function wgt_input_field_callback($args)
{
    $option = get_option($args['label_for']);
    $type = isset($args['type']) ? $args['type'] : 'text';
    echo '<input type="' . esc_attr($type) . '" 
                 id="' . esc_attr($args['label_for']) . '" 
                 name="' . esc_attr($args['label_for']) . '" 
                 value="' . esc_attr($option) . '" 
                 class="regular-text" />';
}

function wgt_select_field_callback($args)
{
    $option = get_option($args['label_for'], '');
    echo '<select id="' . esc_attr($args['label_for']) . '" 
                  name="' . esc_attr($args['label_for']) . '">';
    foreach ($args['options'] as $value => $label) {
        $selected = selected($option, $value, false);
        echo "<option value='" . esc_attr($value) . "' $selected>" . esc_html($label) . "</option>";
    }
    echo '</select>';
}

// Settings page content
function wgt_settings_page_content()
{ ?>
    <div class="wrap wgt-settings-wrap">
        <h1 class="wgt-main-title">WGT Settings</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('wgt_settings_group');
            do_settings_sections('wgt-settings');
            submit_button();
            ?>
        </form>
    </div>

    <style>
        /* Main title */
        .wgt-settings-wrap .wgt-main-title {
            font-size: 28px;
            margin-bottom: 20px;
            color: #23282d;
        }

        /* Section titles (smaller subtitles) */
        .wgt-settings-wrap h2 {
            font-size: 20px;
            margin-top: 25px;
            margin-bottom: 10px;
            color: #0073aa;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        /* Field labels */
        .wgt-settings-wrap th {
            font-weight: 600;
            padding: 10px 10px 10px 0;
        }

        /* Inputs */
        .wgt-settings-wrap input[type="text"],
        .wgt-settings-wrap input[type="password"] {
            width: 400px;
            max-width: 100%;
        }

        /* Submit button spacing */
        .wgt-settings-wrap .submit {
            margin-top: 20px;
        }
    </style>
<?php }

add_filter('show_admin_bar', '__return_false');


// Hook into admin_footer so modal is available on all admin pages
add_action('admin_footer', function () {
?>
    <div id="paymentModal" class="payment-modal" style="display:none;">
        <div class="payment-modal-content">
            <span id="closeModal" class="payment-close">&times;</span>
            <h2>Receive Manual Payment</h2>

            <form id="paymentForm">
                <input type="hidden" name="action" value="save_direct_payment">

                <label class="form-label">Payment ID</label>
                <input class="form-control" type="text" name="payment_id" id="payment_id" readonly>

                <label class="form-label">Country</label>
                <input class="form-control" type="text" name="country" id="country" readonly>

                <label class="form-label">Currency</label>
                <input class="form-control" type="text" name="currency" id="currency" readonly>

                <label class="form-label">Payment Amount</label>
                <input class="form-control" type="text" name="amount" id="amount" readonly>

                <label class="form-label">Payment Date *</label>
                <input class="form-control" type="date" name="payment_date" id="payment_date" required>

                <label class="form-label">Bank Name *</label>
                <input class="form-control" type="text" name="bank_name" id="bank_name" required>

                <label class="form-label">Payment Mode</label>
                <select class="form-control" name="payment_mode" id="payment_mode">
                    <option>Other</option>
                    <option>Online / UPI / NEFT / RTGS</option>
                    <option>Bank Deposit</option>
                    <option>Demand Draft</option>
                    <option>Cash</option>
                    <option>Cheque</option>
                </select>

                <label class="form-label">Receipt No / DD No/ Cheque No / Reference No*</label>
                <input class="form-control" type="text" name="reference_no" id="reference_no" required>

                <button type="submit" class="button btn-primary">Submit</button>
                <button type="button" id="cancelBtn" class="button btn-danger">Cancel</button>
            </form>
        </div>
    </div>
<?php
});

add_action('wp_ajax_save_direct_payment', function () {
    // check_ajax_referer('save_direct_payment_nonce');

    global $wpdb;

    $payment_id   = sanitize_text_field($_POST['payment_id']);
    $country      = sanitize_text_field($_POST['country']);
    $currency     = sanitize_text_field($_POST['currency']);
    $amount       = floatval($_POST['amount']);
    $payment_date = sanitize_text_field($_POST['payment_date']);
    $bank_name    = sanitize_text_field($_POST['bank_name']);
    $payment_mode = sanitize_text_field($_POST['payment_mode']);
    $reference_no = sanitize_text_field($_POST['reference_no']);

    $table = $wpdb->prefix . "payment_directs";

    $inserted = $wpdb->insert($table, [
        'payment_id'   => $payment_id,
        'payment_date' => $payment_date,
        'bank_name'    => $bank_name,
        'payment_mode' => $payment_mode,
        'reference_no' => $reference_no,
        'created_at'   => date('Y-m-d H:i:s'),
        'updated_at'   => date('Y-m-d H:i:s'),
    ]);

    if ($inserted !== false) {
        $updated = $wpdb->update($wpdb->prefix . 'payments', ['status' => 'Paid', 'paid_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')], ['id' => $payment_id]);
        if ($updated !== false) {
            wp_send_json_success("Payment Status Updated Successfully");
        }
    } else {
        wp_send_json_error("DB insert failed");
    }
});

function get_the_payment_modal($payment)
{
    global $wpdb;
    $payment_id = $payment['id'];
    $direct_payment = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}payment_directs WHERE payment_id='{$payment_id}'", ARRAY_A);
    $country      = $payment['country'];
    $currency     = $payment['currency'];
    $amount       = $payment['amount'];
    $bank_name    = '';
    $payment_mode = '';
    $reference_no = '';
    $payment_date = '';
    if (! empty($direct_payment)) {
        $bank_name = $direct_payment['bank_name'];
        $payment_mode = $direct_payment['payment_mode'];
        $reference_no = $direct_payment['reference_no'];
        $payment_date = $direct_payment['payment_date'];
    }
?>
    <button type="button" class="button openPaymentForm"
        data-payment-id="<?php echo esc_attr($payment['id']); ?>"
        data-country="<?php echo esc_attr($country); ?>"
        data-currency="<?php echo esc_attr($currency); ?>"
        data-amount="<?php echo esc_attr($amount); ?>"
        data-bank_name="<?php echo esc_attr($bank_name); ?>"
        data-payment_date="<?php echo esc_attr($payment_date); ?>"
        data-payment_mode="<?php echo esc_attr($payment_mode); ?>"
        data-reference_no="<?php echo esc_attr($reference_no); ?>">
        <?= ($payment['status'] == 'Paid') ? 'Check Direct Payment' : 'Receive Direct Payment'; ?>
    </button>
    <?php if ($payment['status'] == 'Paid') { ?>
        <button type="button" class="button mark_as_unpaid" data-action="mark_as_unpaid" data-payment-id="<?= $payment_id ?>" data-nonce="<?= wp_create_nonce('update_payment_status_nonce'); ?>">Mark As Unpaid</button>
    <?php } ?>

<?php
}

add_action('wp_ajax_update_payment_status', function () {
    check_ajax_referer('update_payment_status_nonce', 'security');

    global $wpdb;
    $payment_id  = sanitize_text_field($_POST['payment_id'] ?? '');

    if (empty($payment_id)) {
        wp_send_json_error("Missing data");
    }

    $table = $wpdb->prefix . "payments"; // <-- change to your payments table

    $updated = $wpdb->update(
        $table,
        ['status' => 'Unpaid'],
        ['id' => $payment_id],
        ['%s'],
        ['%s']
    );

    if ($updated !== false) {
        wp_send_json_success("Status updated");
    } else {
        wp_send_json_error("Database update failed");
    }
});

function disable_save_button_for_old_applications($created_date)
{
    global $new_records_date;
    $created_at = strtotime($created_date);
    $cutoff = strtotime($new_records_date);
    if ($created_at < $cutoff) {
        return true;
    }
    return false;
}

add_action('admin_bar_menu', 'wgt_admin_bar_notification', 100);
function wgt_admin_bar_notification($wp_admin_bar)
{

    if (!current_user_can('manage_options')) return;

    $wp_admin_bar->add_node([
        'id'    => 'wgt_notifications',
        'parent' => 'top-secondary',
        'title' => '
        <span class=" wgt-bell">
            IBR 🔔 <span class="wgt-count">0</span>
        </span>
        <div id="wgt-admin-dropdown" style="display: none; background: #fff; border: 1px solid #ccc; padding: 10px; width: 300px; position: absolute; top: 30px; right: 0px; z-index: 9999;">
            <ul class="notification-list"></ul>
        </div>',
        'href'  => false,
        'meta'  => [
            'class' => 'menupop'
        ]
    ]);
}

add_action('wp_ajax_get_notifications', 'wgt_get_notifications');
add_action('wp_ajax_nopriv_get_notifications', 'wgt_get_notifications');

function wgt_get_notifications()
{
    global $wpdb;

    $table = $wpdb->prefix . 'notifications';
    $user  = wp_get_current_user();

    $where = '';
    if (!in_array('administrator', $user->roles)) {
        $where = $wpdb->prepare(" AND user_id = %d", get_current_user_id());
    }

    $rows = $wpdb->get_results(
        "SELECT id, type, data, created_at
         FROM $table
         WHERE read_at IS NULL
         AND type IN ('PaymentReceivedNotification', 'ApplicationCancelledNotification', 'OrderPaymentReceivedNotification')
         $where
         ORDER BY id DESC
         LIMIT 20"
    );

    $notifications = [];

    foreach ($rows as $row) {

        $data = json_decode($row->data, true);
        if (!$data) continue;

        /* =========================
           Application Cancelled
        ========================== */
        if ($row->type === 'ApplicationCancelledNotification') {

            $notifications[] = [
                'id'      => $row->id,
                'message' => sprintf(
                    'Application for %s with Order/Application ID: %s has been cancelled.',
                    $data['payable']['type'] ?? 'Record',
                    $data['payable']['application_id'] ?? 'N/A'
                ),
                'date' => date('d-M-Y H:i:s', strtotime($row->created_at))
            ];
        } elseif ($row->type === 'PaymentReceivedNotification') {

            $notifications[] = [
                'id'      => $row->id,
                'message' => sprintf(
                    'Payment of %s %s received for %s with Order/Application ID: %s.',
                    $data['currency'] ?? 'INR',
                    $data['amount'] ?? '0.00',
                    $data['payable']['type'] ?? 'Record',
                    $data['payable']['application_id'] ?? 'N/A'
                ),
                'date' => date('d-M-Y H:i:s', strtotime($data['paid_at'] ?? $row->created_at))
            ];
        } elseif ($row->type === 'OrderPaymentReceivedNotification') {

            $notifications[] = [
                'id'      => $row->id,
                'message' => sprintf(
                    'Payment of %s %s received for Order ID: #%s.',
                    $data['currency'] ?? 'INR',
                    $data['amount'] ?? '0.00',
                    $data['payable']['order_id'] ?? 'N/A'
                ),
                'date' => date('d-M-Y H:i:s', strtotime($data['paid_at'] ?? $row->created_at))
            ];
        }
    }

    wp_send_json($notifications);
}

add_action('wp_ajax_mark_notification_read', 'wgt_mark_notification_read');

function wgt_mark_notification_read()
{
    global $wpdb;

    $id = intval($_POST['id']);
    $table = $wpdb->prefix . 'notifications';

    $wpdb->update(
        $table,
        ['read_at' => current_time('mysql')],
        ['id' => $id]
    );

    wp_send_json_success();
}

/**
 * Add payment notification on WooCommerce payment complete
 */
add_action('woocommerce_payment_complete', 'wgt_add_payment_notification');
function wgt_add_payment_notification($order_id) {
    if (!$order_id) return;

    global $common_class;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $amount  = $order->get_total();
    $currency = $order->get_currency();
    $order_number = $order->get_order_number();

    $common_class->add_notification(
        'OrderPaymentReceivedNotification',
        'administrator',
        '1',
        [
            "paid_at"  => current_time('mysql'),
            "amount"   => $amount,
            "currency" => $currency,
            "payable"  => [
                "type" => 'Order',
                "order_id" => $order_number
            ]
        ]
    );
    
}
