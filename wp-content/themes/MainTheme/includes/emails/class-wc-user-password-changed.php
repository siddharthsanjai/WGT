<?php
if (! defined('ABSPATH')) {
    exit;
}

class WC_Email_User_Password_Changed extends WC_Email
{

    public function __construct()
    {
        $this->id             = 'user_password_changed';
        $this->title          = __('User Password Changed', 'your-textdomain');
        $this->description    = __('This email is sent to users when their password has been changed, customized for Membership & Application IDs.', 'your-textdomain');
        $this->template_html  = 'emails/user-password-changed.php';
        $this->template_plain = 'emails/plain/user-password-changed.php';
        $this->template_base  = get_stylesheet_directory() . '/woocommerce/';

        $this->placeholders   = array(
            '{user_name}'      => '',
            '{membership_id}'  => '',
            '{application_id}' => '',
        );

        // Trigger when WooCommerce fires customer password reset
        add_action('woocommerce_user_changed_password', array($this, 'trigger'), 10, 1);

        parent::__construct();

        $this->recipient = ''; // filled dynamically
    }

    public function trigger($user_id = 0)
    {
        if ( ! $user_id && is_admin() && isset($_GET['preview_email']) ) {
            $user_id = 1; // fallback user ID for preview
        }

        // If no user_id passed, but $this->object already set, try using that
        if (! $user_id && isset($this->object->ID)) {
            $user_id = $this->object->ID;
        }

        if (! $user_id) {
            return;
        }

        $user = get_user_by('id', $user_id);
        if (! $user) {
            return;
        }

        $this->object = $user; // Save full WP_User object

        $this->recipient = $user->user_email;
        $this->placeholders['{user_name}']      = $user->display_name;
        $this->placeholders['{membership_id}']  = get_user_meta($user_id, 'membership_id', true) ?: '—';
        $this->placeholders['{application_id}'] = get_user_meta($user_id, 'application_id', true) ?: '—';

        if (! $this->is_enabled() || ! $this->get_recipient()) {
            return;
        }

        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
            $this->get_attachments()
        );
    }



    public function get_default_subject()
    {
        return __('Your password was successfully changed', 'your-textdomain');
    }

    public function get_default_heading()
    {
        return __('Password Changed', 'your-textdomain');
    }

    public function get_content_html() {
    ob_start();
    wc_get_template(
        $this->template_html,
        array(
            'email_heading'  => $this->get_heading(),
            'user'           => $this->object,       // WP_User
            'user_id'        => $this->object->ID,   // Explicitly pass ID
            'membership_id'  => $this->placeholders['{membership_id}'],
            'application_id' => $this->placeholders['{application_id}'],
            'email'          => $this,
        ),
        '',
        $this->template_base
    );
    return ob_get_clean();
}

    public function get_content_plain()
    {
        ob_start();
        wc_get_template(
            $this->template_plain,
            array(
                'email_heading'  => $this->get_heading(),
                'user'           => $this->object, // ✅ add this
                'user_name'      => $this->placeholders['{user_name}'],
                'membership_id'  => $this->placeholders['{membership_id}'],
                'application_id' => $this->placeholders['{application_id}'],
                'email'          => $this,
            ),
            '',
            $this->template_base
        );
        return ob_get_clean();
    }

    public function get_default_recipient() {
        // Pick any existing user as a preview fallback
        $user = get_user_by( 'id', 1 ); // e.g., admin
        return $user ? $user->user_email : 'no-reply@internationalbookofrecords.com';
    }
}
