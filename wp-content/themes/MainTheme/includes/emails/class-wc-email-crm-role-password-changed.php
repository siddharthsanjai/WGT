<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Email_CRM_Role_Password_Changed extends WC_Email {

    public $user_name;
    public $changed_on;

    public function __construct() {
        $this->id             = 'crm_role_password_changed';
        $this->title          = 'CRM Role Password Changed';
        $this->description    = 'This email is sent when a CRM role password has been changed.';
        $this->heading        = 'Password Changed Notification';
        $this->subject        = 'Your password has been changed';
        $this->template_html  = 'emails/crm-role-password-changed.php';
        $this->template_plain = 'emails/plain/crm-role-password-changed.php';
        $this->template_base  = get_template_directory() . '/woocommerce/';

        parent::__construct();

        // Make sure the email is enabled by default
        $this->enabled = true;
    }

    /**
     * Trigger the email
     */
    public function trigger( $user_id ) {
        if ( ! $user_id ) {
            return;
        }

        $user = get_userdata( $user_id );

        $this->user_name  = $user->display_name;
        $this->changed_on = current_time( 'mysql' );
        $this->recipient  = $user->user_email;

        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    public function get_content_html() {
        ob_start();
        wc_get_template(
            $this->template_html,
            array(
                'email_heading' => $this->get_heading(),
                'user_name'     => $this->user_name,
                'changed_on'    => $this->changed_on,
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
        return ob_get_clean();
    }
}
