<?php
// Theme support
function wgt_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
}
add_action('after_setup_theme', 'wgt_setup');

// Enqueue styles
function wgt_enqueue_styles()
{
    if (is_admin() || (isset($_GET['page']) && $_GET['page'] === 'wgt')) {
        return;
    } // Don't load on wp-admin
    global $post;
    $records_participants = get_query_var('records_participants');
    if (function_exists('is_account_page') && is_account_page()) {
        $template_name = 'woocommerce-account';
    } elseif (function_exists('is_cart') && is_cart()) {
        $template_name = 'woocommerce-cart';
    } elseif (function_exists('is_checkout') && is_checkout()) {
        $template_name = 'woocommerce-checkout';
    } elseif (is_page() && $tpl = get_page_template_slug($post->ID)) {
        if ($records_participants) {
            $template_name = 'records-participants';
        } else {
            $template_name = basename($tpl, '.php');
        }
    } elseif (is_singular() && isset($post->post_name)) {
        $template_name = $post->post_name;
    } elseif (is_singular()) {
        $template_name = 'single-' . get_post_type();
    } elseif (is_home()) {
        $template_name = 'home';
    } elseif (is_front_page()) {
        $template_name = 'front-page';
    } elseif (is_archive()) {
        $template_name = 'archive';
    } else {
        return;
    }

    $theme_uri = get_template_directory_uri();
    $theme_path = get_template_directory();

    $styles_dir = '/assets/css/frontend/';
    $scripts_dir = '/assets/js/';

    $css_file = $theme_path . $styles_dir . $template_name . '.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'wgt-' . $template_name . '-style',
            $theme_uri . $styles_dir . $template_name . '.css',
            [],
            filemtime($css_file)
        );
    }

    wp_enqueue_style('wgt_bootstrap', $theme_uri . '/assets/css/bootstrap.min.css', array(), '5.2.3');
    wp_enqueue_style('wgt_flags', $theme_uri . '/assets/css/flags.css', array('wgt_bootstrap'), '1.0');
    wp_enqueue_style('wgt_meanmenu', $theme_uri . '/assets/css/meanmenu.css', array('wgt_flags'), '1.0');
    wp_enqueue_style('wgt_boxicons', $theme_uri . '/assets/css/boxicons.min.css', array('wgt_meanmenu'), '1.0');
    wp_enqueue_style('wgt_aos', $theme_uri . '/assets/css/aos.css', array('wgt_boxicons'), '1.0');
    wp_enqueue_style('wgt_slick', $theme_uri . '/assets/css/slick.css', array('wgt_aos'), '1.0');
    wp_enqueue_style('wgt_main-style', $theme_uri . '/assets/css/style.css', array('wgt_slick'), '1.0');
    wp_enqueue_style('wgt_font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array('wgt_main-style'), '4.7');
    wp_enqueue_style('wgt_jquery-ui-style', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array('wgt_font-awesome'));
    wp_enqueue_style('wgt_select2', $theme_uri . '/assets/css/select2.min.css');
    wp_enqueue_style('datatable', $theme_uri . '/assets/css/datatable.css', array('wgt_select2'), '1.0');
}
add_action('wp_enqueue_scripts', 'wgt_enqueue_styles', 5);

// Enqueue scripts
function wgt_enqueue_scripts()
{
    if (is_admin() || (isset($_GET['page']) && $_GET['page'] === 'wgt')) {
    return;
} // Don't load on wp-admin
    global $post;

    $records_participants = get_query_var('records_participants');
    if (function_exists('is_account_page') && is_account_page()) {
        $template_name = 'woocommerce-account';
    } elseif (function_exists('is_cart') && is_cart()) {
        $template_name = 'woocommerce-cart';
    } elseif (function_exists('is_checkout') && is_checkout()) {
        $template_name = 'woocommerce-checkout';
    } elseif (is_page() && $tpl = get_page_template_slug($post->ID)) {
        if ($records_participants) {
            $template_name = 'records-participants';
        } else {
            $template_name = basename($tpl, '.php');
        }
    } elseif (is_singular() && isset($post->post_name)) {
        $template_name = $post->post_name;
    } elseif (is_singular()) {
        $template_name = 'single-' . get_post_type();
    } elseif (is_home()) {
        $template_name = 'home';
    } elseif (is_front_page()) {
        $template_name = 'front-page';
    } elseif (is_archive()) {
        $template_name = 'archive';
    } else {
        return;
    }

    $theme_uri  = get_template_directory_uri();
    $theme_path = get_template_directory();

    $script_path = '/assets/js/pages/' . $template_name . '.js';
    $full_path   = $theme_path . $script_path;
    wp_enqueue_script('wgt_bootstrap', $theme_uri . '/assets/js/vendor/bootstrap.bundle.js', array('jquery'), null, true);
    if (file_exists($full_path)) {
        wp_enqueue_script(
            'wgt-page-' . $template_name,
            $theme_uri . $script_path,
            ['jquery', 'wgt_bootstrap'],
            filemtime($full_path), // use `time()` instead if preferred
            true
        );
    }
    wp_localize_script('wgt-page-script', 'wgtData', [
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('wgt-nonce'),
        'loginUrl'  => wp_login_url(),
        'themeUrl'  => get_template_directory_uri(),
        // 'currency'  => $store_currency
    ]);


    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-slider');

    // Always enqueue these in admin
    wp_enqueue_script('jquery-validate', get_template_directory_uri() . '/assets/js/jquery.validate.min.js', ['jquery'], '1.20.0', true);
    wp_enqueue_script(
        'jquery-validate-additional',
        get_template_directory_uri() . '/assets/js/additional-methods-min.js',
        ['jquery-validate'],
        '',
        true
    );
    wp_enqueue_script('wgt_modernizr', $theme_uri . '/assets/js/vendor/modernizr.js', array(), null, false);
    wp_enqueue_script('wgt_bootstrap', $theme_uri . '/assets/js/vendor/bootstrap.bundle.js', array('jquery'), null, true);
    wp_enqueue_script('wgt_meanmenu', $theme_uri . '/assets/js/vendor/jquery.meanmenu.js', array('jquery'), null, true);
    wp_enqueue_script('wgt_flagstrap', $theme_uri . '/assets/js/vendor/jquery.flagstrap.min.js', array('jquery'), null, true);
    wp_enqueue_script('wgt_aos', $theme_uri . '/assets/js/vendor/aos.js', array('jquery'), null, true);
    wp_enqueue_script('wgt_slick', $theme_uri . '/assets/js/vendor/slick.min.js', array('jquery'), null, true);
    wp_enqueue_script('wgt_easing', $theme_uri . '/assets/js/vendor/easing.js', array('jquery'), null, true);
    wp_enqueue_script('wgt_select2', $theme_uri . '/assets/js/select2.min.js', ['jquery'], null, true);
    wp_enqueue_script('datatable', $theme_uri . '/assets/js/datatable.js', ['jquery'], null, true);
    wp_enqueue_script('custom_datatable', $theme_uri . '/assets/js/custom_datatable.js', ['jquery', 'datatable'], null, true);
    wp_localize_script('custom_datatable', 'cd_data', [
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('wgt-nonce'),
        'loginUrl'  => wp_login_url(),
        'themeUrl'  => get_template_directory_uri(),
        // 'currency'  => $store_currency
    ]);

    wp_enqueue_script('wgt_main', $theme_uri . '/assets/js/main.js', array('jquery', 'wgt_meanmenu', 'wgt_flagstrap', 'wgt_aos', 'wgt_slick', 'wgt_easing', 'wgt_select2'), null, true);

    $templates_with_phone = ['world-records-apply', 'super-talented-kids-apply', 'apt-women-apply', 'inspiring-humans-apply', 'appreciation-apply', 'records-participants'];
    if (in_array($template_name, $templates_with_phone)) {
        wp_enqueue_style(
            'intl-tel-input-css',
            $theme_uri . '/assets/css/intlTelInput.css',
            [],
            '1.0.0'
        );
        wp_enqueue_script(
            'intl-tel-input',
            $theme_uri . '/assets/js/intlTelInput.min.js',
            ['jquery'],
            '1.0.0',
            true
        );
        wp_enqueue_script(
            'intl-tel-utils',
            $theme_uri . '/assets/js/utils.js',
            ['intl-tel-input'],
            '1.0.0',
            true
        );
        wp_localize_script('wgt-page-' . $template_name, 'wgtData', [
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('wgt-nonce'),
            'loginUrl'  => wp_login_url(),
            'themeUrl'  => get_template_directory_uri(),
            'woocommerce_countries' => WC()->countries->get_countries(),
            'woocommerce_states'    => WC()->countries->get_states(),
            'intlTelInputUtils'     => get_template_directory_uri() . '/assets/js/utils.js',
        ]);
    }
}
add_action('wp_enqueue_scripts', 'wgt_enqueue_scripts', 10);

function wgt_get_country_name_by_code($code)
{
    if (! class_exists('WC_Countries')) {
        return null;
    }

    $countries = new WC_Countries();
    $all_countries = $countries->get_countries();

    $code = strtoupper(trim($code));

    return isset($all_countries[$code]) ? $all_countries[$code] : null;
}

add_action('woocommerce_created_customer', 'wgt_save_registration_fields');
function wgt_save_registration_fields($customer_id)
{
    global $wpdb, $wgt_emails;
    if (isset($_POST['full_name'])) {
        $full_name = sanitize_text_field($_POST['full_name']);
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0];
        $last_name  = isset($name_parts[1]) ? $name_parts[1] : '';

        $last_reg_id = $wpdb->get_var("
            SELECT MAX(CAST(REGEXP_SUBSTR(meta_value, '[0-9]+$') AS UNSIGNED))
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'registration_id'
            AND meta_value LIKE 'IBRXH%'
        ");

        if (!$last_reg_id) {
            $last_reg_id = $wpdb->get_var("
                SELECT 
                MAX(CAST(REGEXP_SUBSTR(registration_id, '[0-9]+$') AS UNSIGNED)) AS max_num
                FROM users
                WHERE registration_id LIKE 'IBRXH%';
            ");
        }
        // Calculate next number
        $next_number = $last_reg_id ? ($last_reg_id + 1) : 1000; // Start from 1000 if none
        $registration_id = 'IBRXH' . $next_number;
        update_user_meta($customer_id, 'first_name', $first_name);
        update_user_meta($customer_id, 'last_name', $last_name);
        update_user_meta($customer_id, 'registration_id', $registration_id);
    }
}

add_action('wp_logout', function () {
    // Detect where logout was triggered
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    if (strpos($referer, admin_url()) !== false) {
        // Logout from wp-admin → redirect to wp-admin login
        wp_safe_redirect(admin_url());
    } else {
        // Logout from frontend → redirect to custom login page
        wp_safe_redirect(home_url('/user/login/'));
    }
    exit;
});

// add_filter('woocommerce_email_enabled_customer_reset_password', '__return_false');
// remove_action('after_password_reset', 'wp_password_change_notification');
// add_filter('send_password_change_email', '__return_false');

// /**
//  * Send custom reset email (works for both WP and WooCommerce).
//  */
// function wgt_send_custom_reset_email($user, $reset_key)
// {
//     global $wgt_emails;

//     if (! $user || is_wp_error($user)) {
//         return;
//     }

//     $reset_link = wgt_get_reset_password_link($user->ID, $reset_key);

//     // Send via your custom email system
//     $wgt_emails->send_by_slug(
//         'password_reset',
//         $user->user_email,
//         [
//             'reset_password_link' => $reset_link,
//             'user_name'           => $user->display_name,
//         ]
//     );
// }

// /**
//  * WooCommerce hook.
//  */
// add_action('woocommerce_customer_reset_password_notification', function ($user, $reset_key) {
//     wgt_send_custom_reset_email($user, $reset_key);
// }, 10, 2);

// /**
//  * WordPress core hook.
//  */
// add_action('retrieve_password_key', function ($reset_key, $user_login) {
//     $user = get_user_by('login', $user_login);
//     if ($user) {
//         wgt_send_custom_reset_email($user, $reset_key);
//     }
// }, 10, 2);



// function wgt_get_reset_password_link($user_id, $reset_key)
// {
//     $user = get_user_by('id', $user_id);
//     if (! $user) {
//         return false;
//     }

//     // WooCommerce my-account reset URL
//     $reset_url = wc_get_endpoint_url(
//         'lost-password',
//         '',
//         wc_get_page_permalink('myaccount')
//     );

//     return add_query_arg([
//         'key' => $reset_key,
//         'id'  => rawurlencode($user->user_login),
//     ], $reset_url);
// }

// add_action('template_redirect', function () {
//     if (is_account_page() && is_wc_endpoint_url('edit-address')) {
//         global $wp;
//         // If no specific address is set in the URL, redirect to billing
//         if (empty($wp->query_vars['edit-address'])) {
//             wp_safe_redirect(wc_get_endpoint_url('edit-address/billing', '', wc_get_page_permalink('myaccount')));
//             exit;
//         }
//     }
// });

add_action('wp_ajax_nopriv_razorpay_payment_success', 'handle_razorpay_payment_success');
add_action('wp_ajax_razorpay_payment_success', 'handle_razorpay_payment_success');

function handle_razorpay_payment_success()
{
    global $wpdb;

    $paymentID = sanitize_text_field($_POST['paymentID']);
    $response  = json_decode(stripslashes($_POST['razorpay_response']), true);

    if (!$paymentID || !$response) {
        wp_send_json_error("Invalid response");
    }

    // Save full response in DB
    $wpdb->update(
        "{$wpdb->prefix}applications",
        [
            'payment_status'      => 'Processing',
            'gateway_reference_id' => $response['razorpay_payment_id'] ?? '',
            'razorpay_order_id'   => $response['razorpay_order_id'] ?? '',
            'razorpay_signature'  => $response['razorpay_signature'] ?? '',
            'razorpay_full_json'  => wp_json_encode($response) // 🔹 store everything
        ],
        ['application_id' => $paymentID]
    );

    // ✅ Capture/verify with Razorpay API
    $razorpay_key    = get_option('razorpay_key_id');
    $razorpay_secret = get_option('razorpay_key_secret');

    $payment_id = $response['razorpay_payment_id'] ?? '';

    if ($payment_id) {
        $url = "https://api.razorpay.com/v1/payments/" . $payment_id . "/capture";
        $args = [
            'body'    => json_encode(['amount' => intval($wpdb->get_var("SELECT amount*100 FROM {$wpdb->prefix}applications WHERE application_id='$paymentID'"))]),
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($razorpay_key . ':' . $razorpay_secret),
                'Content-Type'  => 'application/json'
            ],
            'method'  => 'POST',
            'timeout' => 60
        ];

        $response_api = wp_remote_post($url, $args);

        if (!is_wp_error($response_api)) {
            $body = json_decode(wp_remote_retrieve_body($response_api), true);

            if (!empty($body['status']) && $body['status'] === 'captured') {
                // Update final status
                $wpdb->update(
                    "{$wpdb->prefix}payments",
                    [
                        'payment_captured'      => 1,
                    ],
                    ['id' => $paymentID]
                );
            }
        }
    }

    wp_send_json_success("Payment recorded");
}

/**
 * Newsletter
 */
add_action('wp_ajax_wgt_newsletter_subscribe', 'wgt_newsletter_subscribe');
add_action('wp_ajax_nopriv_wgt_newsletter_subscribe', 'wgt_newsletter_subscribe');

function wgt_newsletter_subscribe()
{
    if (!isset($_POST['email']) || !is_email(trim($_POST['email']))) {
        wp_send_json_error("Invalid email address.");
    }

    global $wpdb;
    $email = sanitize_email($_POST['email']);

    // Prevent duplicates
    $exists = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}newsletters WHERE email = %s", $email)
    );

    if ($exists > 0) {
        wp_send_json_error("You are already subscribed.");
    }

    $inserted = wgt_insert_newsletter($email);

    if ($inserted) {
        wp_send_json_success("Thank you for subscribing!");
    } else {
        wp_send_json_error("Something went wrong. Please try again.");
    }
}

/**
 * Insert a new newsletter subscription into the database.
 *
 * @param string $email The email address to subscribe.
 *
 * @return string|false The UUID of the inserted record if successful, false otherwise.
 */

function wgt_insert_newsletter($email)
{
    global $wpdb; // ✅ Required for db access

    date_default_timezone_set('Asia/Kolkata');

    $data = [
        'id'         => wp_generate_uuid4(),
        'status'     => 'Active',
        'email'      => $email,
        'user_id'    => (is_user_logged_in()) ? get_current_user_id() : null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    $formats = ['%s', '%s', '%s', '%d', '%s', '%s'];

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'newsletters',
        $data,
        $formats
    );

    return $inserted ? $data['id'] : false; // ✅ Return UUID if success
}

// Contact Us Form Submit
add_action('wp_ajax_wgt_send_contact', 'wgt_send_contact');
add_action('wp_ajax_nopriv_wgt_send_contact', 'wgt_send_contact');

function wgt_send_contact()
{
    global $wgt_emails;
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name  = sanitize_text_field($_POST['last_name'] ?? '');
    $email      = sanitize_email($_POST['email'] ?? '');
    $phone      = sanitize_text_field($_POST['phone'] ?? '');
    $message    = sanitize_textarea_field($_POST['message'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($message)) {
        wp_send_json_error("Please fill in all required fields.");
    }

    if (!is_email($email)) {
        wp_send_json_error("Invalid email address.");
    }

    $to = get_option('wgt_enquiry_email'); // send to site admin
    // $subject = "New Contact Form Submission";
    $body = "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width:100%;'>
            <tr>
                <th align='left' style='background:#f4f4f4;'>Name</th>
                <td>{$first_name} {$last_name}</td>
            </tr>
            <tr>
                <th align='left' style='background:#f4f4f4;'>Email</th>
                <td>{$email}</td>
            </tr>
            <tr>
                <th align='left' style='background:#f4f4f4;'>Phone</th>
                <td>{$phone}</td>
            </tr>
            <tr>
                <th align='left' style='background:#f4f4f4;'>Message</th>
                <td>{$message}</td>
            </tr>
        </table>";
    // $headers = ["Reply-To: {$first_name} {$last_name} <{$email}>"];

    $sent = $wgt_emails->send_by_slug(
        'contact_us_form',
        $to,
        [
            'contact_form_submit_details' => $body,
        ]
    );

    if ($sent) {
        wp_send_json_success("Thank you! Your message has been sent.");
    } else {
        wp_send_json_error("Email sending failed. Please try again later.");
    }
}

add_action('wp_ajax_wgt_payment_callback', 'wgt_payment_callback');
add_action('wp_ajax_nopriv_wgt_payment_callback', 'wgt_payment_callback');

function wgt_payment_callback() {
    global $wpdb, $common_class;

    $paymentID = sanitize_text_field($_POST['paymentID'] ?? '');
    $gateway   = strtolower(sanitize_text_field($_POST['gateway'] ?? ''));
    $response  = json_decode(stripslashes($_POST['gateway_response'] ?? ''), true);

    if (!$paymentID || !$gateway) {
        wp_send_json_error(['message' => '⚠️ Missing paymentID or gateway']);
    }

    $payments = new IBR_Payments();

    // Register gateways
    $payments->register_gateway('razorpay', new RazorpayHandler());

    $payment = $payments->get_payment($paymentID);
    $gateway_obj = $payments->get_gateway($gateway);

    if (!$payment || !$gateway_obj) {
        wp_send_json_error(['message' => '⚠️ Invalid payment or gateway']);
    }

    try {
        $result = $gateway_obj->handle_success($payment, $response);

        if (!empty($result['success'])) {
            // ✅ Insert notification
            $common_class->add_notification(
                'PaymentReceivedNotification',
                'administrator',
                '1',
                [
                    "paid_at"  => $response['paid_at'] ?? current_time('mysql'),
                    "amount"   => $response['amount'] ?? $payment->amount,
                    "currency" => $payment->currency,
                    "payable"  => [
                        "type" => ucwords(str_replace('-', ' ', $payment->payable_type)),
                        "application_id" => $payment->application_id
                    ]
                ]
            );

            wp_send_json_success(['message' => $result['message'] ?? '✅ Payment successful']);
        } else {
            // ❌ Insert failure notification
            $common_class->add_notification(
                'PaymentFailedNotification',
                'administrator',
                '0',
                [
                    'amount'   => $payment->amount,
                    'currency' => $payment->currency,
                    'gateway'  => $gateway,
                    'reason'   => $result['message'] ?? 'Unknown failure'
                ]
            );

            wp_send_json_error(['message' => $result['message'] ?? '❌ Payment failed']);
        }
    } catch (Exception $e) {
        $common_class->add_notification(
            'payment_error',
            'administrator',
            '0',
            ['error' => $e->getMessage()]
        );

        wp_send_json_error(['message' => '❌ Error: ' . $e->getMessage()]);
    }
}

/**
 * Enforce form validation on WooCommerce registration
 */
add_action('woocommerce_register_post', 'wgt_validate_strong_password', 10, 3);
function wgt_validate_strong_password($username, $email, $validation_errors)
{
    if (empty($_POST['password'])) {
        $validation_errors->add(
            'empty_password',
            __('Please enter a password.', 'woocommerce')
        );
        return $validation_errors;
    }

    if (isset($_POST['password'])) {
        $password = $_POST['password'];

        // Strong password pattern:
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
        // $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        // if (!preg_match($pattern, $password)) {
        //     $validation_errors->add(
        //         'weak_password',
        //         __('Password must be at least 8 characters long and include one uppercase letter, one lowercase letter, one number, and one special character.', 'woocommerce')
        //     );
        // }

        $pattern = '/^.{6,}$/'; // Allow any password 6 or more characters

        if (!preg_match($pattern, $password)) {
            $validation_errors->add(
                'weak_password',
                __('Password must be at least 6 characters long.', 'woocommerce')
            );
        }
    }

    if (isset($_POST['confirm_password']) && $_POST['password'] !== $_POST['confirm_password']) {
        $validation_errors->add(
            'password_mismatch',
            __('Passwords do not match.', 'woocommerce')
        );
    }

    return $validation_errors;
}

/**
 * Custom WooCommerce registration form submit
 */
add_action('init', 'wgt_handle_custom_customer_register');
function wgt_handle_custom_customer_register() {
    if (
        isset($_POST['register_wgt']) &&
        isset($_POST['email']) &&
        isset($_POST['password']) &&
        wp_verify_nonce($_POST['woocommerce-register-nonce'], 'woocommerce-register')
    ) {
        // Sanitize input
        $email     = sanitize_email($_POST['email']);
        $password  = $_POST['password'];
        $full_name = sanitize_text_field($_POST['full_name']);

        if (empty($email) || empty($password)) {
            wc_add_notice(__('Email and password are required.', 'woocommerce'), 'error');
            return;
        }

        // Check if email exists
        if (email_exists($email)) {
            wc_add_notice(__('This email address is already registered.', 'woocommerce'), 'error');
            return;
        }

        // Split full name
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0];
        $last_name  = isset($name_parts[1]) ? $name_parts[1] : '';

        // Create username from email prefix
        $username = sanitize_user(current(explode('@', $email)));

        // ✅ Create WooCommerce customer (this triggers all Woo hooks correctly)
        $user_id = wc_create_new_customer($email, $username, $password);

        if (is_wp_error($user_id)) {
            wc_add_notice($user_id->get_error_message(), 'error');
            return;
        }

        // Save name
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);

        // ✅ Log the user in
        wc_set_customer_auth_cookie($user_id);

        // ✅ Redirect to My Account
        wp_safe_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
}

function wgt_send_application_received_email_to_user($arg, $module)
{
    global $wgt_emails, $common_class;
    $user = get_user_by('id', $arg['user_id']);
    $user_email = $user->user_email;
    $membership_id = get_user_meta($user->ID, 'registration_id', true);
    $amount     = $common_class->get_currency_conversion_rate($arg['currency']) * $common_class->wgt_get_record_fee($arg['category_fee_id'], 'INR');
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
    $html_data = ['membership_id' => $membership_id, 'application_id' => $arg['application_id'], 'user_name' => $user->first_name . ' ' . $user->last_name, 'site_name' => 'International Book Of Records', 'application_fee' => $arg['currency'] . ' ' . $amount, 'site_url' => site_url()];
    $wgt_emails->send_by_slug($pre . '_application_received', $user_email, $html_data);
}

/**
 * Fix canonical and OG URLs for custom dynamic endpoints
 */
add_action('init', function () {
    // List all dynamic endpoint bases you want handled
    $custom_endpoints = ['records', 'super-talented-kids', 'inspiring-humans', 'apt-women', 'appreciation-awards'];

    // Common callback generator
    $callback = function ($url) use ($custom_endpoints) {
        global $wp;

        foreach ($custom_endpoints as $endpoint) {
            if (strpos($_SERVER['REQUEST_URI'], "/$endpoint/") !== false) {
                $url = home_url(add_query_arg([], $wp->request));
                return trailingslashit($url);
            }
        }

        return $url;
    };

    // --- For Yoast SEO ---
    add_filter('wpseo_canonical', $callback);
    // add_filter('wpseo_opengraph_url', $callback);

});

// AJAX: Add participant certificate to cart
add_action('wp_ajax_add_participant_to_cart', 'add_participant_to_cart');
add_action('wp_ajax_nopriv_add_participant_to_cart', 'add_participant_to_cart');

function add_participant_to_cart() {
    global $wpdb;
    if (
        ! isset($_POST['participant_id']) ||
        ! isset($_POST['record_id']) ||
        ! isset($_POST['quantity']) ||
        ! isset($_POST['product_id'])
    ) {
        wp_send_json_error('Something went wrong');
    }

    $participant_id = sanitize_text_field($_POST['participant_id']);
    $record_id      = sanitize_text_field($_POST['record_id']);
    $quantity       = max(1, intval($_POST['quantity']));
    $product_id     = intval($_POST['product_id']);

    if ( ! $product_id ) {
        wp_send_json_error('Product not found');
    }
    
    $product = wc_get_product($product_id);
    $sku = $product ? $product->get_sku() : '';

    if ($sku == 'wr-participant-certificate') {
        $table = $wpdb->prefix . 'records';
    }

    if ($sku == 'app-participant-certificate') {
        $table = $wpdb->prefix . 'appreciation';
    }

    // ✅ Fetch participant & title names
    $participant_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}participants WHERE id = %s", $participant_id)); // or from custom table/meta
    $record_title     = $wpdb->get_var($wpdb->prepare("SELECT title FROM {$table} WHERE id = %s", $record_id)); // or from custom table/meta
    // ✅ Custom cart item data
    $cart_item_data = array(
        'participant_id'   => $participant_id,
        'participant_name' => $participant_name,
        'record_id'        => $record_id,
        'record_title'     => $record_title,
        'record_type'      => ($sku == 'wr-participant-certificate') ? 'record' : 'appreciation',
    );

    WC()->cart->add_to_cart(
        $product_id,
        $quantity,
        0,
        array(),
        $cart_item_data
    );

    wp_send_json_success(array(
        'participant_name' => $participant_name,
        'record_title'     => $record_title
    ));
}

add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {

    if (isset($cart_item['record_title'])) {
        $item_data[] = array(
            'name'  => 'Title',
            'value' => esc_html($cart_item['record_title']),
        );
    }

    if (isset($cart_item['participant_name'])) {
        $item_data[] = array(
            'name'  => 'Participant',
            'value' => esc_html($cart_item['participant_name']),
        );
    }

    return $item_data;
}, 10, 2);

add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values) {

    if (!empty($values['record_title'])) {
        $item->add_meta_data('Title', $values['record_title'], true);
    }

    if (!empty($values['participant_name'])) {
        $item->add_meta_data('Participant', $values['participant_name'], true);
    }

    if (!empty($values['record_id'])) {
        $item->add_meta_data('Record Id', $values['record_id'], true);
    }

    if (!empty($values['participant_id'])) {
        $item->add_meta_data('Participant Id', $values['participant_id'], true);
    }

    if (!empty($values['record_type'])) {
        $item->add_meta_data('Record Type', $values['record_type'], true);
    }

}, 10, 3);

add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'show_only_title_and_participant_on_thankyou', 10, 2 );
function show_only_title_and_participant_on_thankyou( $formatted_meta, $item ) {

    // ✅ Run ONLY on Order Received (Thank You) page
    if ( ! is_wc_endpoint_url( 'order-received' ) ) {
        return $formatted_meta;
    }

    $allowed_keys = array(
        'Title',
        'Participant'
    );

    foreach ( $formatted_meta as $key => $meta ) {
        if ( ! in_array( $meta->key, $allowed_keys, true ) ) {
            unset( $formatted_meta[$key] );
        }
    }

    return $formatted_meta;
}

add_action('woocommerce_product_options_pricing', function () {

    woocommerce_wp_text_input([
        'id' => '_price_in_india',
        'label' => 'Price for India (₹)',
        'type' => 'number',
        'custom_attributes' => ['step' => '1']
    ]);

    woocommerce_wp_text_input([
        'id' => '_price_outside_india',
        'label' => 'Price Outside India (₹)',
        'type' => 'number',
        'custom_attributes' => ['step' => '1']
    ]);
});

add_action('woocommerce_admin_process_product_object', function ($product) {

    if (isset($_POST['_price_in_india'])) {
        $product->update_meta_data('_price_in_india', wc_clean($_POST['_price_in_india']));
    }

    if (isset($_POST['_price_outside_india'])) {
        $product->update_meta_data('_price_outside_india', wc_clean($_POST['_price_outside_india']));
    }
});

add_action('woocommerce_before_calculate_totals', 'product_specific_country_price', 30);
function product_specific_country_price($cart) {

    if (is_admin() && !defined('DOING_AJAX')) return;
    if (!WC()->customer) return;

    $country = WC()->customer->get_shipping_country();

    foreach ($cart->get_cart() as $cart_item) {

        $product = $cart_item['data'];
        $product_id = $product->get_id();

        $india_price   = get_post_meta($product_id, '_price_in_india', true);
        $outside_price = get_post_meta($product_id, '_price_outside_india', true);

        if (!$india_price && !$outside_price) continue;

        $price_in_inr = ($country === 'IN')
            ? $india_price
            : $outside_price;

        if (!$price_in_inr) continue;

        $converted_price = convert_inr_to_country_currency(
            (float) $price_in_inr,
            $country
        );

        $product->set_regular_price($converted_price);
        $product->set_sale_price('');
        $product->set_price($converted_price);
    }
}


function convert_inr_to_country_currency($price, $country) {

    global $common_class;
    if ($country === 'IN') {
        return round($price, 2);
    }

    $rate = $common_class->get_currency_conversion_rate($common_class->get_currency_code($country)) ?? 0.012;

    return round($price * $rate, 2);
}

add_filter('woocommerce_currency', 'currency_by_country_global');
function currency_by_country_global($currency) {

    global $common_class;
    if ( ! function_exists('WC') || ! WC()->customer ) {
        return $currency;
    }

    $customer = WC()->customer;
    $country  = $customer->get_shipping_country() ?: $customer->get_billing_country();

    if ( ! $country ) {
        return $currency;
    }

    // Country → Currency map
    $cc = $common_class->get_currency_code($country);
    return $cc ?? $currency;
}


add_filter('woocommerce_currency_symbol', 'currency_symbols_global', 10, 2);
function currency_symbols_global($symbol, $currency) {

    global $wpdb;
    $currency_symbol = $wpdb->get_var($wpdb->prepare(
        "SELECT currency_symbol FROM {$wpdb->prefix}countries WHERE currency = %s",
        $currency
    ));
    return $currency_symbol ? $currency_symbol : $symbol;
}

// Checkout changes
add_filter('woocommerce_checkout_fields', 'remove_order_notes_field');
function remove_order_notes_field($fields) {

    // Remove "Add a note to your order"
    unset($fields['order']['order_comments']);

    return $fields;
}

add_action('wp_ajax_load_participants', 'load_participants_ajax');
add_action('wp_ajax_nopriv_load_participants', 'load_participants_ajax');

function load_participants_ajax() {
    global $wpdb;

    $record_id = $_GET['record_id'] ?? '';
    $limit     = 20;
    $page      = intval($_GET['page'] ?? 1);
    $offset    = ($page - 1) * $limit;
    $search    = sanitize_text_field($_GET['search'] ?? '');

    $where_sql = "WHERE particiable_id = %s";
    $params    = [$record_id];

    if (!empty($search)) {
        $where_sql .= " AND name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($search) . '%';
    }

    // Total participants
    $total_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}participants $where_sql",
        ...$params
    ));

    $total_pages = ceil($total_count / $limit);

    // Fetch participants
    $participants = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}participants $where_sql ORDER BY id ASC LIMIT %d OFFSET %d",
        ...array_merge($params, [$limit, $offset])
    ));

    // Participants HTML
    ob_start();
    $i = $offset + 1;
    if ($participants) {
        foreach ($participants as $p) {
            ?>
            <tr class="participant-row" data-participant-id="<?= esc_html($p->id); ?>">
                <td><?= $i++; ?></td>
                <td><?= esc_html($p->name); ?></td>
                <td>
                    <select class="pc-qty quantity-select">
                        <?php for ($j = 1; $j <= 10; $j++): ?>
                            <option><?= $j; ?></option>
                        <?php endfor; ?>
                    </select>
                </td>
                <td>
                    <button
                        type="button"
                        class="pc-holder-btn"
                        data-toggle="modal"
                        data-target="#certificateModal"
                        data-name="<?= esc_html($p->name); ?>"
                        data-title="<?= esc_html(get_the_title($record_id)); ?>">
                        Holder's Certificate
                    </button>
                </td>
                <td>
                    <button class="pc-cart-btn add-to-cart"
                        data-participant-id="<?= $p->id; ?>"
                        data-record-id="<?= $record_id; ?>"
                        data-product-id="<?= wc_get_product_id_by_sku('wr-participant-certificate'); ?>">🛒 Add to Cart
                    </button>
                </td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="5">No participants found.</td></tr>';
    }
    $html = ob_get_clean();

    // Pagination HTML
    ob_start();
    $total_pages = ceil($total_count / $limit);
    $range = 1; // pages to show on each side
    $base_url = remove_query_arg('page');
    if (!empty($search)) $base_url = add_query_arg('search', $search, $base_url);
    ?>
    <ul class="pc-pagination">
        <?php if ($page > 1): ?>
            <li class="prev"><a href="<?= esc_url(add_query_arg('page', $page - 1, $base_url)); ?>">Prev</a></li>
        <?php endif; ?>

        <?php if ($page > $range + 1): ?>
            <li><a href="<?= esc_url(add_query_arg('page', 1, $base_url)); ?>">1</a></li>
            <?php if ($page > $range + 2) echo '<li class="dots">…</li>'; ?>
        <?php endif; ?>

        <?php
        for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++): ?>
            <li class="<?= $i == $page ? 'active' : ''; ?>">
                <a href="<?= esc_url(add_query_arg('page', $i, $base_url)); ?>"><?= $i; ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $total_pages - $range): ?>
            <?php if ($page < $total_pages - $range - 1) echo '<li class="dots">…</li>'; ?>
            <li><a href="<?= esc_url(add_query_arg('page', $total_pages, $base_url)); ?>"><?= $total_pages; ?></a></li>
        <?php endif; ?>

        <?php if ($page < $total_pages): ?>
            <li class="next"><a href="<?= esc_url(add_query_arg('page', $page + 1, $base_url)); ?>">Next</a></li>
        <?php endif; ?>
    </ul>
    <?php
    $pagination = ob_get_clean();

    wp_send_json([
        'html'        => $html,
        'pagination'  => $pagination,
        'total_count' => $total_count
    ]);
}

add_filter('woocommerce_quantity_input_args', function($args, $product) {
    $wr_product_id  = wc_get_product_id_by_sku('wr-participant-certificate');
    $app_product_id = wc_get_product_id_by_sku('app-participant-certificate');
    if (in_array($product->get_id(), [$wr_product_id, $app_product_id])) {
        $args['max_value'] = 10;
    }
    return $args;
}, 10, 2);

add_filter('woocommerce_update_cart_validation', function($passed, $cart_item_key, $values, $quantity) {
    $wr_product_id  = wc_get_product_id_by_sku('wr-participant-certificate');
    $app_product_id = wc_get_product_id_by_sku('app-participant-certificate');
    if (in_array($values['product_id'], [$wr_product_id, $app_product_id]) && $quantity > 10) {
        wc_add_notice("You cannot add more than 10 certificates.", "error");
        return false;
    }
    return true;
}, 10, 4);

add_filter('woocommerce_checkout_get_value', function ($value, $input) {

    // Only set if empty (do NOT override user choice)
    if (!empty($value) || $input !== 'shipping_country') {
        return $value;
    }

    // Check you are on participants page
    if (!get_query_var('record_id') && !get_query_var('records_participants')) {
        return $value;
    }

    // Get record country (adjust this logic to your data source)
    $record_id = get_query_var('record_id');


    // Example: fetch country from DB / meta
    $record_country = get_post_meta($record_id, 'country', true);

    if (!empty($record_country)) {
        return strtoupper($record_country); // IN, AE, US
    }

    return $value;

}, 10, 2);

add_action('wp_ajax_pc_set_shipping_country', 'pc_set_shipping_country');
add_action('wp_ajax_nopriv_pc_set_shipping_country', 'pc_set_shipping_country');

function pc_set_shipping_country() {

    if (empty($_POST['country'])) {
        wp_die();
    }

    $country = sanitize_text_field($_POST['country']);

    if (WC()->customer) {
        WC()->customer->set_shipping_country($country);
        WC()->customer->set_billing_country($country);
        WC()->customer->save();
    }

    wp_die();
}

// Contact Us Form Submit
add_action('wp_ajax_wgt_onspot_form', 'wgt_onspot_form');
add_action('wp_ajax_nopriv_wgt_onspot_form', 'wgt_onspot_form');

function wgt_onspot_form()
{
    global $wgt_emails;

    // Sanitize inputs
    $record_holder_name = sanitize_text_field($_POST['record_holder_name'] ?? '');
    $attempt_date       = sanitize_text_field($_POST['attempt_date'] ?? '');
    $category           = sanitize_text_field($_POST['category'] ?? '');
    $participants       = sanitize_text_field($_POST['participants'] ?? '');
    $city               = sanitize_text_field($_POST['city'] ?? '');
    $state              = sanitize_text_field($_POST['state'] ?? '');
    $country            = sanitize_text_field($_POST['country'] ?? '');
    $contact_number     = sanitize_text_field($_POST['contact_number'] ?? '');
    $description        = sanitize_textarea_field($_POST['description'] ?? '');

    // Required validation
    if (
        empty($record_holder_name) ||
        empty($attempt_date) ||
        empty($category) ||
        empty($city) ||
        empty($state) ||
        empty($country) ||
        empty($contact_number) ||
        empty($description)
    ) {
        wp_send_json_error("Please fill in all required fields.");
    }

    // Participants required only for specific categories
    if (in_array($category, ['Group', 'Mass', 'Commercial'], true) && empty($participants)) {
        wp_send_json_error("Number of participants is required for selected category.");
    }

    // Email recipient (admin)
    $to = get_option('wgt_enquiry_email');

    // Email body
    $body = "
    <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;width:100%;'>
        <tr>
            <th align='left' style='background:#f4f4f4;'>Record Holder Name</th>
            <td>{$record_holder_name}</td>
        </tr>
        <tr>
            <th align='left' style='background:#f4f4f4;'>Date of Attempt</th>
            <td>{$attempt_date}</td>
        </tr>
        <tr>
            <th align='left' style='background:#f4f4f4;'>Category</th>
            <td>{$category}</td>
        </tr>";

    if (!empty($participants)) {
        $body .= "
        <tr>
            <th align='left' style='background:#f4f4f4;'>No. of Participants</th>
            <td>{$participants}</td>
        </tr>";
    }

    $body .= "
        <tr>
            <th align='left' style='background:#f4f4f4;'>City</th>
            <td>{$city}</td>
        </tr>
        <tr>
            <th align='left' style='background:#f4f4f4;'>State</th>
            <td>{$state}</td>
        </tr>
        <tr>
            <th align='left' style='background:#f4f4f4;'>Country</th>
            <td>{$country}</td>
        </tr>
        <tr>
            <th align='left' style='background:#f4f4f4;'>Contact Number</th>
            <td>{$contact_number}</td>
        </tr>
        <tr>
            <th align='left' style='background:#f4f4f4;'>Description</th>
            <td>{$description}</td>
        </tr>
    </table>";

    // Send email using template system
    $sent = $wgt_emails->send_by_slug(
        'onspot_enquiry_form',
        $to,
        [
            'onspot_form_submit_details' => $body,
        ]
    );

    if ($sent) {
        wp_send_json_success("Thank you! Your submission has been sent successfully.");
    } else {
        wp_send_json_error("Email sending failed. Please try again later.");
    }
}
