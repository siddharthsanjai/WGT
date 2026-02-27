<?php
class RazorpayHandler implements PaymentGatewayInterface
{
    private $api_key;
    private $api_secret;
    private $api;

    public function __construct() {
        $this->api_key    = get_option('wgt_razorpay_key');
        $this->api_secret = get_option('wgt_razorpay_secret');

        if (!class_exists('\Razorpay\Api\Api')) {
            require_once get_theme_file_path('vendor/razorpay/razorpay/Razorpay.php');
        }

        try {
            $this->api = new \Razorpay\Api\Api($this->api_key, $this->api_secret);
        } catch (\Exception $e) {
            error_log('Razorpay init failed: ' . $e->getMessage());
            $this->api = null;
        }
    }

    public function create_order($payment) {
        if (!empty($payment->gateway_reference_id)) {
            return $payment->gateway_reference_id;
        }

        if (!$this->api) {
            return null;
        }

        try {
            $amount = intval($payment->amount) * 100;

            $order = $this->api->order->create([
                'receipt'         => $payment->receipt_id,
                'amount'          => $amount,
                'currency'        => $payment->currency,
                'payment_capture' => 1,
            ]);

            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'payments',
                ['gateway_reference_id' => $order['id']],
                ['id' => $payment->id]
            );

            return $order['id'] ?? null;
        } catch (\Exception $e) {
            error_log('Razorpay create_order failed: ' . $e->getMessage());
            return null; // ✅ Graceful fallback
        }
    }

    public function handle_success($payment, $response) {
        global $wpdb;

        if (!$this->api) {
            return ['success' => false, 'message' => '⚠️ Something went wrong, please try again later.'];
        }

        try {
            $attributes = [
                'razorpay_order_id'   => $response['razorpay_order_id'] ?? '',
                'razorpay_payment_id' => $response['razorpay_payment_id'] ?? '',
                'razorpay_signature'  => $response['razorpay_signature'] ?? '',
            ];

            $this->api->utility->verifyPaymentSignature($attributes);

            $wpdb->update(
                $wpdb->prefix . 'payments',
                [
                    'status'           => 'Paid',
                    'response_data'    => maybe_serialize($response),
                    'paid_at'          => current_time('mysql'),
                    'updated_at'       => current_time('mysql'),
                    'payment_captured' => 1
                ],
                ['id' => $payment->id]
            );

            return ['success' => true, 'message' => '✅ Payment successful!'];
        } catch (\Exception $e) {
            error_log('Razorpay handle_success failed: ' . $e->getMessage());
            return ['success' => false, 'message' => '⚠️ Something went wrong, please try again later.'];
        }
    }

    public function handle_failure($payment, $response, $reason = 'Payment failed') {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'payments',
            [
                'status'           => 'failed',
                'gateway_response' => maybe_serialize($response),
                'updated_at'       => current_time('mysql')
            ],
            ['id' => $payment->id]
        );

        return ['success' => false, 'message' => '⚠️ Something went wrong, please try again later.'];
    }
}
