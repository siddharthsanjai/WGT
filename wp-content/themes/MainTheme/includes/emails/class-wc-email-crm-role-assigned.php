<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Email_CRM_Role_Assigned extends WC_Email {

    public function __construct() {
        $this->id = 'crm_role_assigned';
        $this->title = 'CRM Role Assigned';
        $this->description = 'This email is sent when a user is assigned a new CRM role.';

        $this->heading = 'New CRM Role Assigned';
        $this->subject = 'International Book of Records New CRM Role Assigned - Action Required';

        $this->template_html  = 'emails/crm-role-assigned.php';
        $this->template_plain = 'emails/plain/crm-role-assigned.php';

        $this->recipient = ''; // we’ll set dynamically

        parent::__construct();

        $this->template_base = get_stylesheet_directory() . '/woocommerce/';
    }

    public function trigger( $user_email, $user_name, $role_name, $crm_login_link ) {
        $this->recipient = $user_email;

        $this->user_name = $user_name;
        $this->role_name = $role_name;
        $this->crm_login_link = $crm_login_link;

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
                'email_heading'   => $this->get_heading(),
                'user_name'       => $this->user_name,
                'role_name'       => $this->role_name,
                'crm_login_link'  => $this->crm_login_link,
                'email'           => $this,
            ),
            '',
            $this->template_base
        );
        return ob_get_clean();
    }

    public function get_content_plain() {
        ob_start();
        wc_get_template(
            $this->template_plain,
            array(
                'email_heading'   => $this->get_heading(),
                'user_name'       => $this->user_name,
                'role_name'       => $this->role_name,
                'crm_login_link'  => $this->crm_login_link,
                'email'           => $this,
            ),
            '',
            $this->template_base
        );
        return ob_get_clean();
    }
}
