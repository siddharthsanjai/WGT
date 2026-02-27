<?php

function wgt_enqueue_admin_scripts($hook)
{
    global $store_currency;
    // Always enqueue these in admin
    wp_enqueue_script(
        'jquery-validate',
        get_template_directory_uri() . '/assets/js/jquery.validate.min.js',
        ['jquery'],
        '1.20.0',
        true
    );

    wp_enqueue_style(
        'intl-tel-input',
        get_template_directory_uri() . '/assets/css/intlTelInput.css'
    );

    wp_enqueue_script(
        'intl-tel-input',
        get_template_directory_uri() . '/assets/js/intlTelInput.min.js',
        ['jquery'],
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'intl-tel-input-utils',
        get_template_directory_uri() . '/assets/js/utils.js',
        ['intl-tel-input'],
        '1.0.0',
        true
    );
    
    // wp_enqueue_style(
    //     'wgt-notifications-css',
    //     get_theme_file_uri('/assets/css/notifications.css')
    // );

    wp_enqueue_script(
        'wgt-admin-notifications',
        get_theme_file_uri('/assets/js/notifications.js'),
        ['jquery'],
        null,
        true
    );

    wp_localize_script('wgt-admin-notifications', 'wgtNotify', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'sound'   => get_theme_file_uri('/assets/sound/notification.wav')
    ]);

    // Conditionally enqueue only on wgt pages
    if ( strpos($hook, 'wgt') !== false ) {

        wp_enqueue_style(
            'wgt-admin-bootstap',
            get_template_directory_uri() . '/assets/css/bootstrap.min.css'
        );

        wp_enqueue_style(
            'wgt-admin-bootstap',
            get_template_directory_uri() . '/assets/css/summernote.min.css'
        );

        wp_enqueue_style('wgt_select2', get_template_directory_uri() . '/assets/css/select2.min.css');

        wp_enqueue_style(
            'wgt-admin-style',
            get_template_directory_uri() . '/assets/css/admin.css'
        );
        
        wp_enqueue_script(
            'chartjs',
            get_template_directory_uri() . '/assets/js/chart.js',
            [],
            null,
            true
        );
        wp_enqueue_script('wgt-select2', get_template_directory_uri() . '/assets/js/select2.min.js', ['jquery'], null, true);


        wp_enqueue_script(
            'wgt-admin-script',
            get_template_directory_uri() . '/assets/js/admin.js',
            ['jquery', 'chartjs', 'intl-tel-input', 'intl-tel-input-utils', 'wgt-select2'],
            '1.0.0',
            true
        );

        wp_localize_script('wgt-admin-script', 'wgtData', [
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('wgt-nonce'),
            'loginUrl'  => wp_login_url(),
            'themeUrl'  => get_template_directory_uri(),
            // 'woocommerce_countries' => WC()->countries->get_countries(),
            // 'woocommerce_states'    => WC()->countries->get_states(),
            'currency'  => $store_currency,
            'intlTelInputUtilsPath' => get_template_directory_uri() . '/assets/js/utils.js'
        ]);
    }

    wp_enqueue_media(); // in case you need media uploader always
}
add_action('admin_enqueue_scripts', 'wgt_enqueue_admin_scripts');
