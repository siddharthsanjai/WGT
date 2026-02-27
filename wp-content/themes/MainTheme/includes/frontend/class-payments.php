<?php
if (!interface_exists('PaymentGatewayInterface')) {
    interface PaymentGatewayInterface
    {
        public function create_order($payment);
        public function handle_success($payment, $response);
        public function handle_failure($payment, $response);
    }
}

if (!class_exists('IBR_Payments')) {

    class IBR_Payments
    {

        private $wpdb;
        private $table;
        private $gateways = []; // Array of payment gateway handlers

        public function __construct()
        {
            global $wpdb;
            $this->wpdb = $wpdb;
            $this->table = $wpdb->prefix . 'payments';
        }

        /**
         * Register a payment gateway handler
         */
        public function register_gateway($name, $handler)
        {
            $this->gateways[strtolower($name)] = $handler; // handler is a class implementing a standard interface
        }

        /**
         * Fetch payment by ID
         */
        public function get_payment($paymentID)
        {
            $payment = $this->wpdb->get_row(
                $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %s", $paymentID)
            );

            if (!$payment) {
                return null;
            }
            $record_table = '';
            if ($payment->payable_type == 'record') {
                $record_table = 'records';
            } else if ($payment->payable_type == 'apt-woman' || $payment->payable_type == 'apt-women') {
                $record_table = 'apt_women';
            } else if ($payment->payable_type == 'inspiring-human') {
                $record_table = 'inspiring_humans';
            } else if ($payment->payable_type == 'super-talented-kid') {
                $record_table = 'super_talented_kids';
            } else if ($payment->payable_type == 'appreciation') {
                $record_table = 'appreciation';
            }

            // Join with related table based on type
            if (! empty($record_table)) {
                $record = $this->wpdb->get_row(
                    $this->wpdb->prepare(
                        "SELECT user_id, mobile, application_id FROM {$this->wpdb->prefix}{$record_table} WHERE id = %s",
                        $payment->payable_id
                    )
                );
                if ($record) {
                    $payment->user_id = $record->user_id;
                    $payment->mobile  = $record->mobile;
                    $payment->application_id = $record->application_id;
                }
            }

            return $payment;
        }


        /**
         * Create payment order dynamically based on gateway
         */
        public function create_order($paymentID)
        {
            $payment = $this->get_payment($paymentID);
            if (!$payment) return false;

            $gateway_name = strtolower($payment->gateway);
            if (!isset($this->gateways[$gateway_name])) return false;

            $handler = $this->gateways[$gateway_name];
            if ($payment->gateway_reference_id) return $payment->gateway_reference_id;

            $order_id = $handler->create_order($payment);
            // Save gateway reference ID
            if ($order_id) {
                $this->wpdb->update(
                    $this->table,
                    ['gateway_reference_id' => $order_id],
                    ['id' => $paymentID]
                );
            }

            return $order_id;
        }

        /**
         * Handle payment success dynamically
         */
        public function payment_success($paymentID, $response)
        {
            $payment = $this->get_payment($paymentID);
            if (!$payment) return false;

            $gateway_name = strtolower($payment->gateway);
            if (!isset($this->gateways[$gateway_name])) return false;

            $handler = $this->gateways[$gateway_name];
            $handler->handle_success($payment, $response);

            // Mark payment as completed
            $this->wpdb->update(
                $this->table,
                [
                    'status' => 'completed',
                    'gateway_response' => maybe_serialize($response),
                    'paid_at' => current_time('mysql')
                ],
                ['id' => $paymentID]
            );

            // Add notification
            // $this->add_notification('PaymentReceivedNotification', 'administrator', 1, [
            //     "paid_at" => $response['paid_at'] ?? current_time('mysql'),
            //     "amount" => $response['amount'] ?? $payment->amount,
            //     "currency" => "INR",
            //     "payable" => [
            //         "type" => "Record",
            //         "application_id" => "IBR10165"
            //     ]
            // ]);

            return true;
        }

        /**
         * Get latest unread notification
         */
        public function get_latest_notification($user_id = 1)
        {
            global $wpdb;
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}notifications 
                     WHERE notifiable_id = %s AND read_at IS NULL 
                     ORDER BY created_at DESC LIMIT 1",
                    $user_id
                )
            );

            if ($row) return maybe_unserialize($row->data);
            return null;
        }

        // 🔹 Get a gateway by name
        public function get_gateway($name)
        {
            return $this->gateways[$name] ?? null;
        }
    }
}
