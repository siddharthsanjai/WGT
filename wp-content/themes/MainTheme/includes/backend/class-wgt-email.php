<?php
if (! defined('ABSPATH')) exit;

class WGT_Custom_Emails
{
    public function __construct()
    {
        add_action('init', [$this, 'register_cpt']);
        add_action('add_meta_boxes', [$this, 'add_subject_metabox']);
        add_action('add_meta_boxes', [$this, 'add_slug_metabox']);
        add_action('add_meta_boxes', [$this, 'move_editor_metabox']);
        add_action('save_post_wgt_email', [$this, 'save_email_data'], 10, 2);
        add_filter('wp_insert_post_data', [$this, 'validate_required_fields'], 10, 2);
        add_action('admin_notices', [$this, 'admin_notices']);
        add_filter('post_row_actions', [$this, 'add_duplicate_link'], 10, 2);
        add_action('admin_action_wgt_duplicate_email', [$this, 'duplicate_email']);
    }

    public function register_cpt()
    {
        $labels = [
            'name' => 'WGT Emails',
            'singular_name' => 'WGT Email',
            'menu_name' => 'WGT Emails',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New WGT Email',
            'edit_item' => 'Edit WGT Email',
            'new_item' => 'New WGT Email',
            'all_items' => 'All WGT Emails',
            'view_item' => 'View WGT Email',
            'search_items' => 'Search WGT Emails',
            'not_found' => 'No WGT emails found',
            'not_found_in_trash' => 'No WGT emails found in Trash',
        ];

        register_post_type('wgt_email', [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-email-alt',
        ]);
    }

    public function add_subject_metabox()
    {
        add_meta_box('wgt_email_subject', 'Email Subject (Required)', [$this, 'render_subject_metabox'], 'wgt_email', 'normal', 'high');
    }

    public function add_slug_metabox()
    {
        add_meta_box('wgt_email_slug', 'Email Slug', [$this, 'render_slug_metabox'], 'wgt_email', 'side', 'default');
    }

    public function render_subject_metabox($post)
    {
        $subject = get_post_meta($post->ID, '_wgt_email_subject', true);
        echo '<input type="text" name="wgt_email_subject" value="' . esc_attr($subject) . '" class="widefat" required />';
        echo '<p class="description">This will be used as the email subject. </p>';
    }

    public function render_slug_metabox($post)
    {
        $slug = get_post_meta($post->ID, '_wgt_email_slug', true);
        if (!$slug) echo '<em>Slug will be generated automatically on save.</em>';
        else echo '<code>' . esc_html($slug) . '</code><p class="description">Use this slug to send the email programmatically.</p>';
    }

    public function move_editor_metabox()
    {
        remove_meta_box('postdivrich', 'wgt_email', 'normal');
        add_meta_box('wgt_email_content', 'Email Content (Required)', [$this, 'render_editor_metabox'], 'wgt_email', 'normal', 'default');
    }

    public function render_editor_metabox($post)
    {
        $content = $post->post_content;
        wp_editor($content, 'post_content', [
            'textarea_name' => 'post_content',
            'media_buttons' => true,
            'textarea_rows' => 12,
        ]);
        echo '<p class="description">This will be the email body. You can use placeholders like {user_name}, {membership_id}, {application_id}, {site_name}, {payment_link}, {application_fee}, {site_url}. {my_account_url}, {cancel_reason}</p>';
    }

    public function save_email_data($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_type !== 'wgt_email') return;

        $title = isset($_POST['post_title']) ? trim($_POST['post_title']) : '';
        $subject = isset($_POST['wgt_email_subject']) ? trim($_POST['wgt_email_subject']) : '';

        if (empty($title) || !preg_match('/^[A-Za-z ]+$/', $title)) return;

        // Update title and slug
        remove_action('save_post_wgt_email', [$this, 'save_email_data'], 10);
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $title,
            'post_name' => str_replace(' ', '_', strtolower($title)), // spaces to underscores
        ]);
        add_action('save_post_wgt_email', [$this, 'save_email_data'], 10, 2);

        if (!empty($subject)) update_post_meta($post_id, '_wgt_email_subject', sanitize_text_field($subject));

        $slug = $this->ensure_unique_slug(str_replace(' ', '_', strtolower($title)), $post_id);
        update_post_meta($post_id, '_wgt_email_slug', $slug);
    }


    private function ensure_unique_slug($slug, $post_id)
    {
        global $wpdb;
        $base_slug = $slug;
        $i = 1;
        while ($wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s AND post_id != %d LIMIT 1",
            '_wgt_email_slug',
            $slug,
            $post_id
        ))) {
            $slug = $base_slug . '-' . $i;
            $i++;
        }
        return $slug;
    }

    public function validate_required_fields($data, $postarr)
    {
        if ($data['post_type'] !== 'wgt_email' || $data['post_status'] === 'trash') return $data;

        $title = isset($data['post_title']) ? trim($data['post_title']) : '';
        $subject = isset($_POST['wgt_email_subject']) ? trim($_POST['wgt_email_subject']) : '';
        $content = isset($_POST['post_content']) ? trim($_POST['post_content']) : '';

        $errors = [];

        if (empty($title)) {
            $errors[] = 'Email Name (Title) is required.';
        } elseif (!preg_match('/^[A-Za-z ]+$/', $title)) {  // Allow letters and spaces
            $errors[] = 'Email Name (Title) must contain only alphabet characters and spaces (A-Z, a-z).';
        } else {
            global $wpdb;
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND BINARY post_title=%s AND ID != %d LIMIT 1",
                'wgt_email',
                $title,
                isset($postarr['ID']) ? intval($postarr['ID']) : 0
            ));
            if ($existing) {
                $errors[] = 'Email Name (Title) must be unique. Another email with this title exists.';
            }
        }

        if (empty($subject)) $errors[] = 'Email Subject is required.';
        if (empty($content)) $errors[] = 'Email Content is required.';

        if (!empty($errors) && !empty($postarr['ID'])) {
            set_transient('wgt_email_errors_' . $postarr['ID'], $errors, 30);
        }

        return $data;
    }


    public function admin_notices()
    {
        global $post;
        if ($post && $post->post_type === 'wgt_email') {
            $errors = get_transient('wgt_email_errors_' . $post->ID);
            if ($errors) {
                foreach ($errors as $error) echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
                delete_transient('wgt_email_errors_' . $post->ID);
            }
        }
    }

    public static function send($post_id, $to, $data = [])
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'wgt_email') return false;
        $subject = get_post_meta($post_id, '_wgt_email_subject', true);
        $content = $post->post_content;
        return self::send_with_content($to, $subject, $content, $data);
    }

    public static function send_by_slug($slug, $to, $data = [])
    {
        $post_id = self::get_post_id_by_slug($slug);
        if (!$post_id) return false;
        return self::send($post_id, $to, $data);
    }

    private static function get_post_id_by_slug($slug)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s LIMIT 1",
            '_wgt_email_slug',
            $slug
        ));
    }

    private static function send_with_content($to, $subject, $content, $data = [])
    {
        $placeholders = array_merge(
            [
                'site_name'      => get_bloginfo('name'),
                'my_account_url' => get_site_url('my-account'),
            ], $data
        );
        foreach ($placeholders as $tag => $value) {
            $subject = str_replace('{' . $tag . '}', $value, $subject);
            $content = str_replace('{' . $tag . '}', $value, $content);
        }
        $content = apply_filters('the_content', $content);
        $content = self::get_email_template($content);
        add_filter('wp_mail_content_type', [__CLASS__, 'set_mail_content_type']);
        $headers = [
            'From: International Book of Records <no-reply@internationalbookofrecords.com>',
            'Reply-To: no-reply@internationalbookofrecords.com',
            'Content-Type: text/html; charset=UTF-8'
        ];
        $sent = wp_mail($to, $subject, $content, $headers);
        remove_filter('wp_mail_content_type', [__CLASS__, 'set_mail_content_type']);
        return $sent;
    }

    private static function get_email_template($dynamic_content)
    {
        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>body{margin:0;padding:0;font-family:Arial,sans-serif;background:#f4f4f4;}.email-container{max-width:600px;margin:0 auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 5px rgba(0,0,0,0.1);}.email-footer{text-align:center;padding-top:20px;border-top:1px solid #ddd;font-size:12px;color:#888;}</style>
        </head><body><div class="email-container"><div class="email-body">' . $dynamic_content . '</div><div class="email-footer">For any query mail us at <a href="mailto:enquiry@internationalbookofrecords.com">enquiry@internationalbookofrecords.com</a></div></div></body></html>';
    }

    public static function set_mail_content_type()
    {
        return 'text/html';
    }

    public function add_duplicate_link($actions, $post)
    {
        if ($post->post_type === '_wgt_email') {
            $url = wp_nonce_url(
                add_query_arg([
                    'action' => 'wgt_duplicate_email',
                    'post'   => $post->ID
                ], admin_url('admin.php')),
                'wgt_duplicate_email'
            );
            $actions['duplicate'] = '<a href="' . esc_url($url) . '" title="Duplicate this email">Duplicate</a>';
        }
        return $actions;
    }

    public function duplicate_email()
    {
        if (!isset($_GET['post']) || !current_user_can('edit_posts')) {
            wp_die('No permission to duplicate.');
        }

        check_admin_referer('wgt_duplicate_email');

        $post_id = intval($_GET['post']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== '_wgt_email') {
            wp_die('Invalid email to duplicate.');
        }

        // Duplicate post
        $new_post = [
            'post_title'   => $post->post_title . ' Copy',
            'post_content' => $post->post_content,
            'post_status'  => 'draft',
            'post_type'    => '_wgt_email',
        ];

        $new_post_id = wp_insert_post($new_post);

        if ($new_post_id) {
            // Copy meta
            $meta_keys = ['_wgt_email_subject', '_wgt_email_slug'];
            foreach ($meta_keys as $meta_key) {
                $value = get_post_meta($post_id, $meta_key, true);
                if ($meta_key === '_wgt_email_slug') {
                    // Ensure unique slug
                    $value = $this->ensure_unique_slug($value, $new_post_id);
                }
                update_post_meta($new_post_id, $meta_key, $value);
            }

            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        }

        wp_die('Error duplicating email.');
    }
}

$GLOBALS['wgt_emails'] = new WGT_Custom_Emails();
