<?php
/**
 * MainTheme Functions
 */

function maintheme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height' => 60,
        'width' => 200,
        'flex-height' => true,
        'flex-width' => true,
    ));

    // HTML5 Support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'maintheme'),
        'footer' => __('Footer Menu', 'maintheme'),
    ));

}
add_action('after_setup_theme', 'maintheme_setup');

function maintheme_enqueue_assets()
{
if (is_admin()) {
    return;
}
    $theme_uri = get_template_directory_uri();
    $version = wp_get_theme()->get('Version');


    wp_enqueue_style('bootstrap', $theme_uri . '/assets/css/bootstrap.min.css', array(), $version);
    wp_enqueue_style('slicknav', $theme_uri . '/assets/css/slicknav.min.css', array(), $version);
    wp_enqueue_style('swiper', $theme_uri . '/assets/css/swiper-bundle.min.css', array(), $version);
    wp_enqueue_style('fontawesome', $theme_uri . '/assets/css/all.min.css', array(), $version);
    wp_enqueue_style('animate', $theme_uri . '/assets/css/animate.css', array(), $version);
    wp_enqueue_style('magnific-popup', $theme_uri . '/assets/css/magnific-popup.css', array(), $version);
    wp_enqueue_style('mousecursor', $theme_uri . '/assets/css/mousecursor.css', array(), $version);
    wp_enqueue_style('custom-style', $theme_uri . '/assets/css/custom.css', array(), $version);
    wp_enqueue_style('admin.css', $theme_uri . '/assets/css/admin.css', array(), $version);

    // Main style.css
    wp_enqueue_style('maintheme-style', get_stylesheet_uri(), array(), $version);



    // WordPress built-in jQuery
    wp_enqueue_script('jquery');

    wp_enqueue_script(
        'jquery-validation',
        $theme_uri . '/assets/js/jquery.validate.min.js',
        array('jquery'),
        '1.19.5',
        true
    );
    wp_enqueue_script('bootstrap', $theme_uri . '/assets/js/bootstrap.min.js', array('jquery'), $version, true);
    // wp_enqueue_script('validator', $theme_uri . '/assets/js/validator.min.js', array('jquery'), $version, true);
    wp_enqueue_script('slicknav', $theme_uri . '/assets/js/jquery.slicknav.js', array('jquery'), $version, true);
    wp_enqueue_script('swiper', $theme_uri . '/assets/js/swiper-bundle.min.js', array(), $version, true);
    wp_enqueue_script('waypoints', $theme_uri . '/assets/js/jquery.waypoints.min.js', array('jquery'), $version, true);
    wp_enqueue_script('counterup', $theme_uri . '/assets/js/jquery.counterup.min.js', array('jquery'), $version, true);
    wp_enqueue_script('magnific-popup', $theme_uri . '/assets/js/jquery.magnific-popup.min.js', array('jquery'), $version, true);
    wp_enqueue_script('smoothscroll', $theme_uri . '/assets/js/SmoothScroll.js', array(), $version, true);
    wp_enqueue_script('parallaxie', $theme_uri . '/assets/js/parallaxie.js', array('jquery'), $version, true);
    wp_enqueue_script('gsap', $theme_uri . '/assets/js/gsap.min.js', array(), $version, true);
    wp_enqueue_script('magiccursor', $theme_uri . '/assets/js/magiccursor.js', array('jquery'), $version, true);
    wp_enqueue_script('splittext', $theme_uri . '/assets/js/SplitText.js', array(), $version, true);
    wp_enqueue_script('scrolltrigger', $theme_uri . '/assets/js/ScrollTrigger.min.js', array(), $version, true);
    wp_enqueue_script('ytplayer', $theme_uri . '/assets/js/jquery.mb.YTPlayer.min.js', array('jquery'), $version, true);
    wp_enqueue_script('wow', $theme_uri . '/assets/js/wow.min.js', array(), $version, true);

    // Main Theme JS
    wp_enqueue_script('maintheme-main', $theme_uri . '/assets/js/function.js', array('jquery'), $version, true);

}
add_action('wp_enqueue_scripts', 'maintheme_enqueue_assets');



function maintheme_widgets_init()
{

    register_sidebar(array(
        'name' => __('Sidebar', 'maintheme'),
        'id' => 'sidebar-1',
        'description' => __('Add widgets here.', 'maintheme'),
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));

}
add_action('widgets_init', 'maintheme_widgets_init');

function maintheme_allow_svg($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'maintheme_allow_svg');

// Add nav-item class to <li>
function add_nav_item_class($classes, $item, $args)
{
    if ($args->theme_location == 'primary') {
        $classes[] = 'nav-item';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_nav_item_class', 10, 3);


// Add nav-link class to <a>
function add_nav_link_class($atts, $item, $args)
{
    if ($args->theme_location == 'primary') {
        $atts['class'] = 'nav-link';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_nav_link_class', 10, 3);

//admin meni for wgt


/**
 * IBR Theme functions and definitions
 */



$GLOBALS['store_currency'] = 'INR';

require_once get_template_directory() . '/includes/include-files.php';
// Add front-end functions
require_once get_template_directory() . '/includes/frontend/functions.php';
require_once get_template_directory() . '/includes/backend/scripts.php';
require_once get_template_directory() . '/includes/backend/functions.php';
require_once get_template_directory() . '/includes/backend/class-wgt-email.php';

// Add admin menu
function wgt_add_admin_menu()
{
    add_menu_page(
        'WGT', // Page title
        'WGT', // Menu title
        'access_wgt_menu', // Capability
        'wgt', // Menu slug
        'wgt_pages_content', // Function to display the page
        'dashicons-awards', // Icon
        30 // Position
    );

    // // Add submenu pages
    // add_submenu_page(
    //     'wgt',
    //     'Dashboard',
    //     'Dashboard',
    //     'access_wgt_menu',
    //     'wgt',
    //     'wgt_pages_content'
    // );

    // add_submenu_page(
    //     '',
    //     '',
    //     'manage_options',
    //     'edit_record',
    //     'render_edit_record_page',
    //     'dashicons-edit',
    //     30
    // );

    add_submenu_page(
        'wgt', // Parent slug (matches your main menu)
        'Category Fees', // Page title
        'Category Fees', // Menu title (what appears in the submenu)
        'access_wgt_menu', // Capability (same as parent)
        'wgt-category-fees', // Submenu slug (unique)
        'wgt_category_fees_page_content' // Callback function to display the page
    );

}
add_action('admin_menu', 'wgt_add_admin_menu');

// Dashboard page callback
function wgt_pages_content()
{
    include_once get_template_directory() . '/page-templates/admin/wgt.php';
}

// Create custom tables on theme activation
function wgt_create_custom_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Custom roles table
    $table_name = $wpdb->prefix . 'wgt_custom_roles';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        role_name varchar(50) NOT NULL,
        permissions text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // User meta table for additional fields
    $table_name = $wpdb->prefix . 'wgt_user_meta';
    $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mobile_no varchar(20),
        gender varchar(10),
        profile_photo varchar(255),
        status varchar(20) DEFAULT 'active',
        custom_role_id mediumint(9),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'wgt_create_custom_tables');

// Send email notification for new role assignment
function wgt_send_role_assignment_email($user_id, $role_name)
{
    $user = get_userdata($user_id);
    $to = $user->user_email;
    $subject = 'International Book of Records New CRM Role Assigned – Action Required';

    $message = "Hi {$user->display_name},\n\n";
    $message .= "You've been assigned a new role in the CRM: {$role_name}.\n\n";
    $message .= "For security, please log in and change your password using the link below:\n";
    $message .= "👉 " . wp_login_url() . "\n\n";
    $message .= "If you have any questions, contact International Book of Records team.\n\n";
    $message .= "Thanks,\n";
    $message .= "International Book of Records";

    wp_mail($to, $subject, $message);
}

// Send password change confirmation email
function wgt_send_password_change_email($user_id)
{
    $user = get_userdata($user_id);
    $to = $user->user_email;
    $subject = '✅ Your International Book of Records CRM Password Has Been Changed';

    $message = "Hi {$user->display_name},\n\n";
    $message .= "This is to confirm that your CRM password was successfully changed on " . current_time('Y-m-d H:i:s') . ".\n\n";
    $message .= "If you made this change, no further action is needed.\n";
    $message .= "If you did not request this change, please contact support immediately.\n\n";
    $message .= "Stay secure,\n";
    $message .= "CRM Admin Team\n";
    $message .= "International Book of Records";

    wp_mail($to, $subject, $message);
}
add_action('password_reset', 'wgt_send_password_change_email');

function wgt_add_viewer_role()
{
    add_role(
        'viewer',                     // role key
        'Viewer',                     // display name
        [
            'read' => true,           // can view the dashboard
            'edit_posts' => false,    // cannot edit posts
            'delete_posts' => false,  // cannot delete posts
            'publish_posts' => false, // cannot publish posts
        ]
    );
}
add_action('init', 'wgt_add_viewer_role');

function get_user_profile_image($user_id)
{
    $image         = get_avatar_url(0, ['default' => 'mystery']);
    $profile_image = get_user_meta($user_id, 'profile_photo', true);

    if (! empty($profile_image)) {
        $profile_image_url = wp_get_attachment_url($profile_image);
        if (! empty($profile_image_url)) {
            $image = $profile_image_url;
        }
    }
    return $image;
}

/**
 * Custom Email Class
 */
add_filter( 'woocommerce_email_classes', 'add_custom_crm_emails' );
function add_custom_crm_emails( $email_classes ) {

    // Include the CRM Password Changed class
    require_once get_template_directory() . '/includes/emails/class-wc-email-crm-role-password-changed.php';
    $email_classes['WC_Email_CRM_Role_Password_Changed'] = new WC_Email_CRM_Role_Password_Changed();

    // Include another custom CRM email class, e.g., new-role-assigned
    require_once get_template_directory() . '/includes/emails/class-wc-email-crm-role-assigned.php';
    $email_classes['WC_Email_CRM_Role_Assigned'] = new WC_Email_CRM_Role_Assigned();

    // Include another custom CRM email class, e.g., new-role-assigned
    require_once get_template_directory() . '/includes/emails/class-wc-user-password-changed.php';
    $email_classes['WC_Email_User_Password_Changed'] = new WC_Email_User_Password_Changed();

    // Include another custom CRM email class, e.g., new-role-assigned
    require_once get_template_directory() . '/includes/emails/class-wc-email-received-email.php';
    $email_classes['WC_Email_Received_Email'] = new WC_Email_Received_Email();

    return $email_classes;
}

add_action('init', function() {
    $wgt_cap = 'access_wgt_menu';

    $roles = ['editor', 'viewer', 'administrator'];
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            if (! $role->has_cap('read')) {
                $role->add_cap('read');
            }
            if (! $role->has_cap($wgt_cap)) {
                $role->add_cap($wgt_cap);
            }
        }
    }
});

add_filter( 'woocommerce_prevent_admin_access', function( $prevent_access ) {
    if ( current_user_can('access_wgt_menu') ) {
        return false;
    }
    return $prevent_access;
});

add_action('admin_menu', function() {
    if ( current_user_can('access_wgt_menu') && !current_user_can('administrator') ) {
        global $menu;
        foreach ($menu as $k => $item) {
            if ($item[2] !== 'wgt') {
                remove_menu_page($item[2]);
            }
        }
    }
}, 99);

function wgt_has_edit_access() {
    $role = wp_get_current_user()->roles[0];
    if (in_array($role, ['editor', 'administrator'])) {
        return true;
    }
        
    return false;
}

// add_filter('authenticate', 'migrate_legacy_passwords', 20, 3);
function migrate_legacy_passwords($user, $username, $password) {
    if (is_a($user, 'WP_User')) {
        return $user; // normal login succeeded
    }

    $user = get_user_by('login', $username);
    if (!$user) {
        return null;
    }

    $legacy_hash = get_user_meta($user->ID, 'legacy_password', true);
    if ($legacy_hash && password_verify($password, $legacy_hash)) {
        // if matches legacy hash, update to WordPress
        wp_set_password($password, $user->ID);
        return get_user_by('id', $user->ID);
    }

    return null;
}

function wgt_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('menus');
    add_theme_support('woocommerce');
    register_nav_menu('primary', __('Primary Menu', 'wgt'));
    register_nav_menu('secondary', __('Footer Menu', 'wgt'));
}
add_action('after_setup_theme', 'wgt_theme_setup');

function wgt_wc_enqueue_scripts() {
    // Deregister WordPress's default jQuery
    wp_deregister_script('jquery');

    // Enqueue local jQuery
    wp_register_script('jquery', get_template_directory_uri() . '/assets/js/jquery.min.js', [], null, true);
    wp_enqueue_script('jquery');

    // CSS
    wp_enqueue_style('bootstrap-css', get_template_directory_uri() . '/assets/css/bootstrap.min.css');
    wp_enqueue_style('owl-carousel-min', get_template_directory_uri() . '/assets/css/owl.carousel.min.css');
    wp_enqueue_style('owl-carousel-default', get_template_directory_uri() . '/assets/css/owl.theme.default.min.css');
    wp_enqueue_style('theme-css', get_template_directory_uri() . '/assets/css/theme.css');
    wp_enqueue_style('main-style', get_stylesheet_uri());

    // JS
    wp_enqueue_script( 'wc-add-to-cart' );
    wp_enqueue_script( 'wc-cart-fragments' );
    wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js', ['jquery'], null, true);
    wp_enqueue_script('owl-carousel', get_template_directory_uri() . '/assets/js/owl.carousel.min.js', array('jquery'), null, true);
    wp_enqueue_script('custom-js', get_template_directory_uri() . '/assets/js/custom.js', ['jquery', 'wc-add-to-cart', 'wc-cart-fragments'], '1.0', true);

    // Enqueue jQuery UI components provided by WordPress
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');

    wp_enqueue_script('jquery-validate', get_template_directory_uri() . '/assets/js/jquery.validate.min.js', ['jquery'], '1.0', true);

    // Enqueue intlTelInput script and style
    wp_enqueue_script('intlTelInput', get_template_directory_uri() . '/assets/js/intlTelInput.min.js', ['jquery'], '1.0', true);
    wp_enqueue_style('intlTelInput-css', get_template_directory_uri() . '/assets/css/intlTelInput.min.css', [], '1.0');

    wp_enqueue_script('wishlist', get_template_directory_uri() . '/assets/js/wishlist.js', ['jquery', 'intlTelInput'], '1.0', true);

    wp_localize_script('wishlist', 'wishlist_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wishlist_nonce'),
        'theme_url' => get_template_directory_uri()
    ]);

    // Enqueue jQuery UI CSS (ensure this path is correct)
    wp_enqueue_style('jquery-ui-css', get_template_directory_uri() . '/assets/css/jquery-ui.css', [], '1.0');

}
// add_action('wp_enqueue_scripts', 'wgt_wc_enqueue_scripts');


add_filter('theme_page_templates', 'custom_register_page_templates');
add_filter('template_include', 'custom_load_page_template');

function custom_register_page_templates($templates) {
    
    $custom_dir = get_theme_file_path('page-templates/public');
    $files = glob($custom_dir . '/*.php');

    if ($files) {
        foreach ($files as $file) {
            $basename = basename($file);
            $template_path = 'page-templates/public/' . $basename;

            $data = get_file_data($file, ['Template Name' => 'Template Name']);
            if (!empty($data['Template Name'])) {
                $templates[$template_path] = $data['Template Name'];
            }
        }
    }

    return $templates;
}

function custom_load_page_template($template) {
    global $wp_query;
    
    if (is_page()) {
        global $post;

        if (! $post instanceof WP_Post) {
            return $template; // safety
        }

        // Get the template slug assigned in Page Editor
        $page_template = get_page_template_slug($post->ID);

        if ($page_template && locate_template($page_template)) {
            return locate_template($page_template);
        }
    }

    return $template;
};

/**
 * Add rewrite rules for world-records
 */
function wgt_world_record_rewrite() {
    // Add record_id as query var
    add_rewrite_tag('%record_id%', '([^/]+)');

    // Reserved slugs that should be handled as real WP pages
    $reserved = '(?:apply|explore)';

   //Participants Page
    add_rewrite_rule(
        '^appreciation-awards/(?!' . $reserved . ')([^/]+)/participants/?$',
        'index.php?pagename=appreciation-awards&record_id=$matches[1]&records_participants=1',
        'top'
    );

    add_rewrite_rule(
        '^records/(?!' . $reserved . ')([^/]+)/participants/?$',
        'index.php?pagename=records&record_id=$matches[1]&records_participants=1',
        'top'
    );

    // 🔹 World Records
    add_rewrite_rule(
        '^records/(?!' . $reserved . ')([^/]+)/?$',
        'index.php?pagename=records&record_id=$matches[1]',
        'top'
    );

    // 🔹 Apt Women
    add_rewrite_rule(
        '^apt-women/(?!' . $reserved . ')([^/]+)/?$',
        'index.php?pagename=apt-women&record_id=$matches[1]',
        'top'
    );

    // 🔹 Super Talented Kids
    add_rewrite_rule(
        '^super-talented-kids/(?!' . $reserved . ')([^/]+)/?$',
        'index.php?pagename=super-talented-kids&record_id=$matches[1]',
        'top'
    );

    // 🔹 Inspiring Humans
    add_rewrite_rule(
        '^inspiring-humans/(?!' . $reserved . ')([^/]+)/?$',
        'index.php?pagename=inspiring-humans&record_id=$matches[1]',
        'top'
    );

    // 🔹 Appreciation
    add_rewrite_rule(
        '^appreciation-awards/(?!' . $reserved . ')([^/]+)/?$',
        'index.php?pagename=appreciation-awards&record_id=$matches[1]',
        'top'
    );
}
add_action('init', 'wgt_world_record_rewrite');


/**
 * Register query vars
 */
function wgt_world_record_query_vars($vars) {
    $vars[] = 'record_id';
    $vars[] = 'records_participants';
    return $vars;
}
add_filter('query_vars', 'wgt_world_record_query_vars');

/**
 * Template handling for dynamic records
 */
function wgt_world_record_template($template) {
    $record_id = get_query_var('record_id');
    $records_participants = get_query_var('records_participants');
    $pagename  = get_query_var('pagename'); // base page slug
    if ($record_id && $records_participants) {
        $new_template = locate_template('page-templates/public/records-participants.php');
        if ($new_template) return $new_template;
    }

    // ✅ Only override when it's a dynamic record URL
    if (!empty($record_id) && !empty($pagename)) {
        switch ($pagename) {
            case 'records':
                $new_template = locate_template('page-templates/public/single-world-record-page.php');
                break;

            case 'apt-women':
                $new_template = locate_template('page-templates/public/single-apt-women-page.php');
                break;

            case 'super-talented-kids':
                $new_template = locate_template('page-templates/public/single-super-talented-kids-page.php');
                break;

            case 'inspiring-humans':
                $new_template = locate_template('page-templates/public/single-inspiring-humans-page.php');
                break;

            case 'appreciation-awards':
                $new_template = locate_template('page-templates/public/single-appreciation-page.php');
                break;

            default:
                $new_template = '';
        }

        if ($new_template) {
            return $new_template;
        }
    }

    // ✅ Otherwise keep default (admin-selected template or WP default)
    return $template;
}
add_filter('template_include', 'wgt_world_record_template', 99);

add_action('template_redirect', function () {
    if (trim($_SERVER['REQUEST_URI'], '/') === 'user') {
        wp_redirect(site_url(), 301); // 301 = permanent redirect
        exit;
    }
});

function wgt_update_currency_rates() {
    global $wpdb;
    $table = $wpdb->prefix . "conversions"; // your table name
    $api_key = '18c25599c1ca3908b7214f044e59188c'; // replace with your actual API key

    // Fetch all target currencies (where "from" is INR)
    $currencies = $wpdb->get_results("SELECT id, `to` FROM {$table} WHERE `from` = 'INR'");
    if (empty($currencies)) {
        return;
    }

    // Build comma-separated list of currency codes
    $currency_list = implode(',', wp_list_pluck($currencies, 'to'));


    // Prepare API endpoint
    $endpoint = "https://api.exchangerate.host/change?access_key={$api_key}&currencies={$currency_list}&source=INR";

    $response = wp_remote_get($endpoint, ['timeout' => 20]);

    if (is_wp_error($response)) {
        error_log("Currency API error: " . $response->get_error_message());
        return;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($body['quotes'])) {
        foreach ($currencies as $currency) {
            $key = "INR{$currency->to}"; // API returns keys like "INRUSD"
            if (isset($body['quotes'][$key])) {
                $change_data = $body['quotes'][$key];

                $wpdb->update(
                    $table,
                    [
                        'value'        => floatval($change_data['end_rate']),  // latest rate
                        'updated_at'   => current_time('mysql')
                    ],
                    ['id' => $currency->id],
                    ['%f', '%s'],
                    ['%d']
                );
            }
        }
    } else {
        error_log("Currency API returned no quotes: " . wp_remote_retrieve_body($response));
    }
}

add_action('rest_api_init', function () {
    register_rest_route('currency/v1', '/update', [
        'methods'  => 'GET',
        'callback' => 'wgt_update_currency_rates_endpoint',
        'permission_callback' => function($request) {
            $key = $request->get_param('key'); // get key from URL
            return $key === '23482398093423908432498';
        },
    ]);
});

function wgt_update_currency_rates_endpoint($request) {
    // Call your existing function
    wgt_update_currency_rates();
    return [
        'status' => 'success',
        'message' => 'Currency rates updated successfully',
        'time' => current_time('mysql')
    ];
}

// Add Google Tag Manager to head
function add_gtm_to_head() {
    ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-T9R5D7Q');</script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action('wp_head', 'add_gtm_to_head', 0);

// Add Google Tag Manager (noscript) immediately after opening body tag
function add_gtm_after_body() {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T9R5D7Q"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action('wp_body_open', 'add_gtm_after_body');

function wgt_category_fees_page_content() {
    global $wpdb;

    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'records';

    // Fetch data for the current tab
    $rows = $wpdb->get_results(
        $wpdb->prepare("
            SELECT 
                c.id AS category_id,
                c.module,
                c.name AS category_name,
                MAX(CASE WHEN f.country = 'India' THEN f.amount END) AS india_amount,
                MAX(CASE WHEN f.country = 'Other' THEN f.amount END) AS other_amount
            FROM 
                wp_categories AS c
            LEFT JOIN 
                wp_category_fees AS f ON c.id = f.category_id
            WHERE 
                c.module = %s
            GROUP BY 
                c.id, c.module, c.name
            ORDER BY 
                c.id ASC
        ", $tab),
        ARRAY_A
    );
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Category Fees</h1>
        <hr class="wp-header-end">

        <h2 class="nav-tab-wrapper">
            <?php
            $tabs = ['records', 'appreciation'];
            foreach ($tabs as $t) {
                $active = ($t === $tab) ? 'nav-tab-active' : '';
                echo '<a href="?page=wgt-category-fees&tab=' . esc_attr($t) . '" class="nav-tab ' . esc_attr($active) . '">' . ucfirst(str_replace('-', ' ', (string)$t)) . '</a>';
            }
            ?>
        </h2>

        <table class="wp-list-table widefat fixed striped" id="wgt-category-fees-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Fees in India (INR)</th>
                    <th>Fees Outside India (INR)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)) : ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr data-id="<?php echo esc_attr($row['category_id']); ?>">
                            <td><strong><?php echo esc_html($row['category_name']); ?></strong></td>
                            <td class="editable" data-field="fee_india">
                                <span class="value"><?php echo esc_html($row['india_amount']); ?></span>
                                <span class="actions">
                                    <button class="edit button button-small"><span class="dashicons dashicons-edit"></span></button>
                                </span>
                            </td>
                            <td class="editable" data-field="fee_outside">
                                <span class="value"><?php echo esc_html($row['other_amount']); ?></span>
                                <span class="actions">
                                    <button class="edit button button-small"><span class="dashicons dashicons-edit"></span></button>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">No categories found for this tab.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <style>
        .editable { position: relative; }
        .editable .actions { margin-left: 8px; }
        .editable input { width: 120px; }
        .editable .save, .editable .cancel { margin-left: 4px; }
    </style>

    <script>
    jQuery(document).ready(function($) {
        const nonce = '<?php echo wp_create_nonce('wgt_update_fee_nonce'); ?>';

        // Edit button clicked
        $('#wgt-category-fees-table').on('click', '.edit', function(e) {
            e.preventDefault();
            const $cell = $(this).closest('.editable');
            const value = $cell.find('.value').text().trim();

            $cell.data('original', value);
            $cell.html(`
                <input type="number" step="0.01" class="fee-input" value="${value}">
                <button class="save button button-small"><span class="dashicons dashicons-yes"></span></button>
                <button class="cancel button button-small"><span class="dashicons dashicons-no-alt"></span></button>
            `);
        });

        // Cancel button clicked
        $('#wgt-category-fees-table').on('click', '.cancel', function(e) {
            e.preventDefault();
            const $cell = $(this).closest('.editable');
            const original = $cell.data('original');
            restoreCell($cell, original);
        });

        // Save button clicked
        $('#wgt-category-fees-table').on('click', '.save', function(e) {
            e.preventDefault();
            const $cell = $(this).closest('.editable');
            const $row = $cell.closest('tr');
            const id = $row.data('id');
            const field = $cell.data('field');
            const value = $cell.find('input').val();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wgt_update_category_fee',
                    nonce: nonce,
                    id: id,
                    field: field,
                    value: value
                },
                beforeSend: function() {
                    $cell.css('background-color', '#fff3cd');
                },
                success: function(res) {
                    if (res.success) {
                        restoreCell($cell, value);
                        $cell.css('background-color', '#d4edda');
                    } else {
                        alert(res.data || 'Failed to update');
                        restoreCell($cell, $cell.data('original'));
                        $cell.css('background-color', '#f8d7da');
                    }
                    setTimeout(() => $cell.css('background-color', ''), 1000);
                },
                error: function() {
                    alert('AJAX error occurred');
                    restoreCell($cell, $cell.data('original'));
                    $cell.css('background-color', '#f8d7da');
                    setTimeout(() => $cell.css('background-color', ''), 1000);
                }
            });
        });

        // Restore the cell to normal display
        function restoreCell($cell, value) {
            $cell.html(`
                <span class="value">${value}</span>
                <span class="actions">
                    <button class="edit button button-small"><span class="dashicons dashicons-edit"></span></button>
                </span>
            `);
        }
    });
    </script>
    <?php
}


// Helper to highlight active tab
function wgt_active_tab($tab) {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    return $current_tab === $tab ? 'nav-tab-active' : '';
}

add_action('wp_ajax_wgt_update_category_fee', 'wgt_update_category_fee');
function wgt_update_category_fee() {
    global $wpdb;

    // Security check
    check_ajax_referer('wgt_update_fee_nonce', 'nonce');

    // Get and sanitize inputs
    $id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $field  = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
    $value  = isset($_POST['value']) ? floatval($_POST['value']) : 0;

    if (!$id || !in_array($field, ['fee_india', 'fee_outside'], true)) {
        wp_send_json_error('Invalid data.');
    }

    // Determine the country
    $country = ($field === 'fee_india') ? 'India' : 'Other';

    $table = $wpdb->prefix . 'category_fees';

    // Check if record exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE category_id = %d AND country = %s",
        $id,
        $country
    ));

    if ($exists) {
        // Update existing record
        $updated = $wpdb->update(
            $table,
            ['amount' => $value],
            ['category_id' => $id, 'country' => $country],
            ['%f'],
            ['%d', '%s']
        );
    } else {
        // Insert new record
        $updated = $wpdb->insert(
            $table,
            [
                'category_id' => $id,
                'country'     => $country,
                'amount'      => $value
            ],
            ['%d', '%s', '%f']
        );
    }

    if ($updated !== false) {
        wp_send_json_success('Fee updated successfully.');
    } else {
        wp_send_json_error('Failed to update fee.');
    }
}

// Razorpay Webhook Handler
add_action('rest_api_init', function () {
    register_rest_route('razorpay/v1', '/webhook', [
        'methods'  => 'POST',
        'callback' => 'wgt_handle_razorpay_webhook',
        'permission_callback' => '__return_true',
    ]);
});

function wgt_handle_razorpay_webhook(WP_REST_Request $request)
{
    date_default_timezone_set('Asia/Kolkata');
    global $wpdb, $common_class;
    $table = $wpdb->prefix . 'razorpay_webhooks';

    $body = $request->get_body();
    
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('[Razorpay Webhook] ❌ Invalid JSON');
        return new WP_REST_Response(['error' => 'Invalid JSON'], 400);
    }

    $event = sanitize_text_field($data['event']);
    $payment_id = $data['payload']['payment']['entity']['id'] ?? null;
    $order_id = $data['payload']['payment']['entity']['order_id'] ?? null;

    if ($event == "payment.captured") {
        $insert = $wpdb->insert(
            $table,
            [
                'payment_id' => $payment_id,
                'order_id' => $order_id,
                'payload' => wp_json_encode($data),
                'received_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s']
        );

        $get_payment_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, status FROM {$wpdb->prefix}payments WHERE gateway_reference_id = %s",
                $order_id
            )
        );
        if ($get_payment_data['status'] != 'Paid') {
            $payments = new IBR_Payments();
            $payments_table = $payments->get_payment($get_payment_data['id']);
            $response = json_decode($data['payload'], true);
            $common_class->add_notification(
                'PaymentReceivedNotification',
                'administrator',
                '1',
                [
                    "paid_at"  => $response['paid_at'] ?? current_time('mysql'),
                    "amount"   => $response['amount'] ?? $payments_table->amount,
                    "currency" => $payments_table->currency,
                    "payable"  => [
                        "type" => ucwords(str_replace('-', ' ', $payments_table->payable_type)),
                        "application_id" => $payments_table->application_id
                    ]
                ]
            );
        }
        
        $update = $wpdb->update(
            $wpdb->prefix . 'payments',  
            [ 
                'status' => 'Paid',
                'payment_captured' => '1',
                'response_data' => wp_json_encode($data),
                'paid_at' => current_time('mysql')
            ],
            [ 'gateway_reference_id' => $order_id ]
        );
    }

    return new WP_REST_Response(['success' => true], 200);
}

add_filter('validate_password_reset', function ($errors, $user) {

    // Only run on reset requests where pass1 is present
    if (! isset($_POST['password_1']) ) {
        return $errors;
    }

    $password = trim( wp_unslash( $_POST['password_1'] ) );

    // Enforce only a 6-char minimum server-side
    if ( strlen( $password ) < 6 ) {
        // Ensure there is an error about length
        $errors->add(
            'password_too_short_custom',
            __('Password must be at least 6 characters long.', 'textdomain')
        );
        return $errors;
    }

    // Remove common password-related error codes added by core / WooCommerce / plugins
    $codes_to_unset = [
        'password_reset_strength',
        'password_reset_mismatch',
        'password_reset_empty',
        'password_too_weak',
        'password_reset',
        'weak_password',
        'password_too_short',
        'password_reset_error',
    ];

    foreach ( $codes_to_unset as $code ) {
        if ( isset( $errors->errors[ $code ] ) ) {
            unset( $errors->errors[ $code ] );
        }
    }

    // If there are no other errors left, return a clean WP_Error object (no errors)
    if ( empty( $errors->errors ) ) {
        // Return an empty WP_Error instance
        return new WP_Error();
    }

    return $errors;
}, 9999, 2); // high priority so this runs after other validators

// add_action('wp_print_scripts', function () {
//     if (is_account_page()) {
//         wp_dequeue_script('wc-password-strength-meter');
//     }
// }, 100);

/**
 * Missed Payment Creation Schedule Cron Job
 */
add_action( 'wp', 'wgt_missed_payment_creation_schedule' );
function wgt_missed_payment_creation_schedule() {

    if ( ! wp_next_scheduled( 'wgt_missed_payment_creation_event' ) ) {

        // Schedule for today at 1:00 AM
        $timestamp = strtotime('today 01:00');

        // If 1:00 AM has already passed, schedule for tomorrow
        if ( $timestamp <= time() ) {
            $timestamp = strtotime('tomorrow 01:00');
        }

        wp_schedule_event( $timestamp, 'daily', 'wgt_missed_payment_creation_event' );
    }
}

add_action( 'wgt_missed_payment_creation_event', 'wgt_missed_payment_creation_event_task' );
function wgt_missed_payment_creation_event_task() {
    global $wpdb, $world_record_manager, $stk_record_manager, $aptw_record_manager, $ih_record_manager, $appreciation_record_manager;

    // World Records
    $records = $wpdb->get_results("SELECT r.* FROM {$wpdb->prefix}records r LEFT JOIN wp_payments p ON p.payable_id = r.id WHERE p.payable_id IS NULL AND DATE(r.created_at) > '2025-10-10' AND r.created_at < (NOW() - INTERVAL 1 HOUR) ORDER BY r.created_at ASC;", ARRAY_A);
    if (!empty($records)) {
        foreach ($records as $record) {
            // Create a missed payment entry
            $created = $world_record_manager->create_payment($record['id'], $record);
        }
    }

    // Super Talented Kids
    $stk_records = $wpdb->get_results("SELECT r.* FROM {$wpdb->prefix}super_talented_kids r LEFT JOIN wp_payments p ON p.payable_id = r.id WHERE p.payable_id IS NULL AND DATE(r.created_at) > '2025-10-10' AND r.created_at < (NOW() - INTERVAL 1 HOUR) ORDER BY r.created_at ASC;", ARRAY_A);
    if (!empty($stk_records)) {
        foreach ($stk_records as $stk) {
            // Create a missed payment entry
            $created = $stk_record_manager->create_payment($stk['id'], $stk);
        }
    }

    // Apt Women
    $aptw_records = $wpdb->get_results("SELECT r.* FROM {$wpdb->prefix}apt_women r LEFT JOIN wp_payments p ON p.payable_id = r.id WHERE p.payable_id IS NULL AND DATE(r.created_at) > '2025-10-10' AND r.created_at < (NOW() - INTERVAL 1 HOUR) ORDER BY r.created_at ASC;", ARRAY_A);
    if (!empty($aptw_records)) {
        foreach ($aptw_records as $aptw) {
            // Create a missed payment entry
            $created = $aptw_record_manager->create_payment($aptw['id'], $aptw);
        }
    }

    $ih_records = $wpdb->get_results("SELECT r.* FROM {$wpdb->prefix}inspiring_humans r LEFT JOIN wp_payments p ON p.payable_id = r.id WHERE p.payable_id IS NULL AND DATE(r.created_at) > '2025-10-10' AND r.created_at < (NOW() - INTERVAL 1 HOUR) ORDER BY r.created_at ASC;", ARRAY_A);
    if (!empty($ih_records)) {
        foreach ($ih_records as $ih) {
            // Create a missed payment entry
            $created = $ih_record_manager->create_payment($ih['id'], $ih);
        }
    }

    $app_records = $wpdb->get_results("SELECT r.* FROM {$wpdb->prefix}appreciation r LEFT JOIN wp_payments p ON p.payable_id = r.id WHERE p.payable_id IS NULL AND DATE(r.created_at) > '2025-10-10' AND r.created_at < (NOW() - INTERVAL 1 HOUR) ORDER BY r.created_at ASC;", ARRAY_A);
    if (!empty($app_records)) {
        foreach ($app_records as $app) {
            // Create a missed payment entry
            $created = $appreciation_record_manager->create_payment($app['id'], $app);
        }
    }

    // Schedule next day at 1:00 AM
    $next_run = strtotime('tomorrow 01:00');
    wp_schedule_single_event( $next_run, 'wgt_missed_payment_creation_event' );
}

//email verification
add_action('template_redirect', function() {
    if (isset($_POST['register_wgt'])) {
        $email = sanitize_email($_POST['email']);
        $name  = sanitize_text_field($_POST['full_name']);

        if (!is_email($email)) {
            wc_add_notice('Invalid email address', 'error');
            return;
        }

        
        $token = wp_generate_password(32, false);

        
        set_transient('verify_' . $token, ['email' => $email, 'name' => $name], 60*60);

       

        $verification_link = add_query_arg(['verify_token' => $token], site_url('/verify-email/'));
        wp_mail($email, 'Verify your account', "Click here to verify: $verification_link");

        echo $verification_link;

        wc_add_notice('Please check your email to verify your account.', 'success');
    }
});

//reset email
add_action('template_redirect', function() {
    if (isset($_POST['verify_email'])) {
        $token = sanitize_text_field($_POST['verify_token']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        


        $data = get_transient('verify_' . $token);
        if (!$data) {
            wc_add_notice('Invalid or expired verification token.', 'error');
            return;
        }

       
        if ($password !== $confirm_password) {
            wc_add_notice('Passwords do not match.', 'error');
            return;
        }

        
        $user = get_user_by('email', $data['email']);
        if (!$user) {
            wc_add_notice('User not found.', 'error');
            return;
        }

        
        wp_set_password($password, $user->ID);

        
        delete_transient('verify_' . $token);

       
        wp_redirect('/user/login/');
        exit;
    }
});


//for book page template



add_action('init', function () {
    add_rewrite_rule(
        '^books/([^/]+)/?$',
        'index.php?pagename=book-details&book_slug=$matches[1]',
        'top'
    );
});




add_filter('query_vars', function ($vars) {
    $vars[] = 'book_slug';
    return $vars;
});




add_filter('template_include', function ($template) {
    if (get_query_var('book_slug')) {
        $new = locate_template('page-templates/public/single-product-page.php');
        if ($new) return $new;
    }
    return $template;
});




function custom_book_permalink($permalink, $product) {
    if (has_term('book-listing', 'product_cat', $product->get_id())) {
        return home_url('/books/' . $product->get_slug() . '/');
    }
    return $permalink;
}
add_filter('woocommerce_product_get_permalink', 'custom_book_permalink', 99, 2);

