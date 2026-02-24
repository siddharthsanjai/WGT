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


add_action('admin_menu', 'wgt_register_admin_menu');



function wgt_register_admin_menu()
{
    add_menu_page(
        'WGT Admin',
        'WGT',
        'manage_options',
        'wgt',
        'wgt_admin_page_callback',
        'dashicons-awards',
        30
    );
}

function wgt_admin_page_callback()
{
    // Let wgt.php handle the tab routing from the URL
    include get_template_directory() . '/page-templates/admin/wgt.php';
}

function wgt_has_edit_access()
{
    $current_user = wp_get_current_user();

    // Quick safety check to ensure roles array exists and isn't empty
    if (!empty($current_user->roles)) {
        $role = $current_user->roles[0];
        if (in_array($role, ['editor', 'administrator'])) {
            return true;
        }
    }

    return false;
}



add_action('admin_enqueue_scripts', 'wgt_enqueue_admin_scripts');

function wgt_enqueue_admin_scripts($hook)
{

    $theme_uri = get_template_directory_uri();

    // jQuery (already loaded in admin, but safe)
    wp_enqueue_script('jquery');

    // jQuery Validate
    wp_enqueue_script(
        'jquery-validate',
        $theme_uri . '/assets/js/jquery.validate.min.js',
        array('jquery'),
        '1.19.5',
        true
    );

    // Flatpickr
    wp_enqueue_script(
        'flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr',
        array(),
        null,
        true
    );

    wp_enqueue_style(
        'flatpickr-css',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css'
    );

    // Select2
    wp_enqueue_script(
        'select2',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
        array('jquery'),
        '4.1.0',
        true
    );

    wp_enqueue_style(
        'select2-css',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
    );

    wp_enqueue_script(
        'intl-tel-input',
        'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/js/intlTelInput.min.js',
        array('jquery'),
        '17.0.19',
        true
    );

    wp_enqueue_style(
        'intl-tel-input-css',
        'https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.19/build/css/intlTelInput.min.css',
        array(),
        '17.0.19'
    );
    // WordPress media uploader
    wp_enqueue_media();

    // Your main admin JS
    wp_enqueue_script(
        'wgt-admin-js',
        get_template_directory_uri() . '/assets/js/admin.js',
        array('jquery'),
        '1.0',
        true
    );

     wp_localize_script('wgt-admin-js', 'wgtdata', [
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('wgt-nonce'),
            'loginUrl'  => wp_login_url(),
            'themeUrl'  => get_template_directory_uri(),
        ]);
 }


