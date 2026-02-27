<?php

if (!defined('ABSPATH')) exit;

class WC_Email_Verified extends WC_Email {

    public function __construct() {
        $this->id             = 'custom_email';
        $this->title          = 'Custom Email';
        $this->description    = 'This email is sent for custom notifications.';
        $this->template_html  = 'emails/custom-email-template.php';
        $this->template_plain = 'emails/plain/custom-email-template.php';
        $this->template_base  = get_stylesheet_directory() . '/woocommerce/';

        $this->recipient      = '';

        add_action('send_custom_email_action', [$this, 'trigger'], 10, 2);

        parent::__construct();
    }

    public function trigger($email_to, $user_name = '') {
        if (!$email_to) return;

        $this->recipient = $email_to;
        $this->user_name = $user_name;

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), []);
    }

    public function get_subject() {
        return 'Email Successfully Verified';
    }

    public function get_heading() {
        return 'Email Verified';
    }

    public function get_content_html() {
        ob_start();
        wc_get_template(
            $this->template_html,
            ['email_heading' => $this->get_heading(), 'user_name' => $this->user_name, 'email' => $this->recipient],
            '',
            $this->template_base
        );
        return ob_get_clean();
    }

    public function get_content_plain() {
        return "Dear {$this->user_name}, your email {$this->recipient} has been successfully verified.";
    }
}
