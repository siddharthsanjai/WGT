<?php

class AppreciationRecordManager
{
    protected $record_table;
    protected $holder_table;
    protected $image_table;
    protected $video_table;
    protected $payment_table;

    public function __construct()
    {
        date_default_timezone_set('Asia/Kolkata');
        global $wpdb;
        $this->record_table = $wpdb->prefix . 'appreciation';
        $this->holder_table = $wpdb->prefix . 'holders';
        $this->image_table  = $wpdb->prefix . 'images';
        $this->video_table  = $wpdb->prefix . 'videos';
        $this->payment_table = $wpdb->prefix . 'payments';
        add_action('init', array($this, 'handle_appreciation_submission'));
        add_action('init', array($this, 'register_appreciation_endpoint'));
        add_filter('woocommerce_account_menu_items', array($this, 'add_appreciation_my_account_page'), 10, 2);
        add_action('woocommerce_account_appreciations_endpoint', array($this, 'appreciation_my_account_page'));
        add_action('wp_ajax_edit_appreciation_section', array($this, 'edit_appreciation_section'));
    }

    /**
     * Save a new World Record.
     */
    public function create($data)
    {
        global $wpdb, $common_class;

        // Prevent duplicate insert
        if (!empty($data['record_id']) && $this->exists($data['record_id'])) {
            return false;
        }

        // Generate next application ID
        $application_id = $this->generateApplicationId();
        $uuid = wp_generate_uuid4();

        $consent_form_id   = 0;
        $consent_form_name = '';

        if (!empty($_FILES['consent_form_file']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('consent_form_file', 0);
            if (!is_wp_error($attachment_id)) {
                $consent_form_id   = $attachment_id;
                $consent_form_name = basename(get_attached_file($attachment_id));
            } else {
                error_log('Consent form upload error: ' . $attachment_id->get_error_message());
            }
        }

        $arg = [
            'id' => $uuid,
            'application_id'     => $application_id,
            'user_id'            => get_current_user_id(),
            'category_fee_id'    => wgt_get_cat_fee_id($data['applying_as'], wgt_get_country_name_by_code($data['country']), 'appreciation'),
            'purpose_id'         => intval($data['purpose']),
            // 'title'              => sanitize_text_field($data['title']),
            'attempt_title'      => '',
            'slug'               => NULL,
            'date'               => date('Y-m-d'),
            'estimate_participants' => NULL,
            'country'            => sanitize_text_field(wgt_get_country_name_by_code($data['country'])),
            'currency'           => $common_class->get_currency_code($data['country']),
            'currency_symbol'    => $common_class->get_currency_symbol($data['country']),
            'state'              => sanitize_text_field($data['state']),
            'city'               => sanitize_text_field($data['city']),
            'address'            => sanitize_text_field($data['address']),
            'zipcode'            => sanitize_text_field($data['zipcode']),
            'cc'                 => sanitize_text_field($data['phone_country_code']),
            'mobile'             => sanitize_text_field($data['phone_number']),
            'description'        => sanitize_text_field(wp_unslash($data['description'])), // sanitize_text_field($data['description']),
            'consent_form'       => $consent_form_id,      // store attachment ID
            'consent_form_name'  => $consent_form_name,    // store actual file name
            'status'             => 'Active',
            'approval_status'    => 'Under Review',
            'terms'              => 1,
            'seen'               => 0,
            'created_at'         => date('Y-m-d H:i:s'),
        ];
        $insert = $wpdb->insert($this->record_table, $arg);

        if ($insert) {
            $this->update_holder_data($uuid, $data);
            $this->create_payment($uuid, $arg);
            wgt_send_application_received_email_to_user($arg, 'appreciation');
        }
        return $insert ? $application_id : false;
    }

    /**
     * Check if a record exists by ID.
     */
    public function exists($record_id)
    {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->record_table} WHERE id = %s",
            $record_id
        ));
    }

    /**
     * Generate the next unique application ID like "IBR10001".
     */
    protected function generateApplicationId()
    {
        global $wpdb;

        $latest = $wpdb->get_var(
            "SELECT application_id 
            FROM {$this->record_table} 
            WHERE application_id LIKE 'IBRAP%' 
            ORDER BY created_at DESC 
            LIMIT 1"
        );

        if (preg_match('/IBRAP(\d+)/', $latest, $matches)) {
            $next = intval($matches[1]) + 1;
        } else {
            $next = 1;
        }

        return 'IBRAP' . $next;
    }

    function handle_appreciation_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wgt_appreciation_submit'])) {
            if (!isset($_POST['wgt_appreciation_nonce']) || !wp_verify_nonce($_POST['wgt_appreciation_nonce'], 'wgt_appreciation_nonce_submit')) {
                wp_die('Security check failed');
            }

            $data = $_POST;
            global $appreciation_record_manager;
            if (!empty($data['record_id'])) {
                // UPDATE
                $updated = $appreciation_record_manager->update($data['record_id'], $data);
                if ($updated) {
                    $_SESSION['wgt_notice'] = "<div class='notice notice-success'>Record updated successfully.</div>";
                } else {
                    $_SESSION['wgt_notice'] = "<div class='notice notice-error'>Update failed. Record not found or no change made.</div>";
                }
            } else {
                // CREATE
                $application_id = $appreciation_record_manager->create($data);
                if ($application_id) {
                    $_SESSION['wgt_notice'] = "<div class='notice notice-success'>Record submitted successfully. ID: $application_id</div>";
                } else {
                    $_SESSION['wgt_notice'] = "<div class='notice notice-error'>Record submission failed. Possible duplicate.</div>";
                }
            }

            // Redirect to avoid resubmission
            wp_redirect(
                add_query_arg([
                    'record_submitted' => '1',
                    'id' => $application_id, // make sure $record_id is defined
                ], wp_get_referer())
            );
            exit;
        }
    }

    function register_appreciation_endpoint()
    {
        add_rewrite_endpoint('appreciations', EP_ROOT | EP_PAGES);
    }

    function add_appreciation_my_account_page($items)
    {
        // Add custom tabs after 'dashboard'
        $new = [];

        foreach ($items as $key => $label) {
            $new[$key] = $label;

            if ($key === 'apt-womens') {
                $new['appreciations'] = 'Appreciation Awards';
            }
        }
        return $new;
    }

    function appreciation_my_account_page()
    {
        include get_stylesheet_directory() . '/woocommerce/myaccount/appreciations.php';
    }

    function update_holder_data($record_id, $data, $module = 'appreciation')
    {
        global $wpdb;

        $table = $this->holder_table;
        $now   = date('Y-m-d H:i:s');

        // Delete old rows for this record_id (so we can reinsert cleanly)
        $wpdb->delete($table, ['holdable_id' => $record_id]);

        // --- Holder 1 ---
        if (!empty($data['holder_name'])) {
            $wpdb->insert($table, [
                'id'            => wp_generate_uuid4(),
                'index'         => 0,
                'holdable_type' => $module,
                'holdable_id'   => $record_id,
                'name'          => $data['holder_name'],
                'parent_name'   => !empty($data['holder_parent_name']) ? $data['holder_parent_name'] : null,
                'dob'           => !empty($data['dob']) ? $data['dob'] : null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // --- Holder 2 ---
        if (!empty($data['second_holder_name'])) {
            $wpdb->insert($table, [
                'id'            => wp_generate_uuid4(),
                'index'         => 1,
                'holdable_type' => $module,
                'holdable_id'   => $record_id,
                'name'          => $data['second_holder_name'],
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // --- Holder 3 ---
        if (!empty($data['company_name'])) {
            $wpdb->insert($table, [
                'id'            => wp_generate_uuid4(),
                'index'         => 0,
                'holdable_type' => $module,
                'holdable_id'   => $record_id,
                'name'          => $data['company_name'],
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        return true;
    }

    function get_record_with_holders($record_id)
    {
        global $wpdb;

        $records_table = $this->record_table;   // main records table
        $holders_table = $this->holder_table;    // holder table
        $images_table = $this->image_table;
        $videos_table = $this->video_table;   // holder table
        $payment_table = $this->payment_table;  // holder table

        // Get main record
        $record = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$records_table} WHERE id = %s", $record_id),
            ARRAY_A
        );

        if (!$record) {
            return null; // record not found
        }

        // Get related holders ordered by index
        $holders = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$holders_table} WHERE holdable_id = %s ORDER BY `index` ASC", $record_id),
            ARRAY_A
        );

        // Attach holders to record
        $record['holders'] = $holders ?: [];

        $images = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$images_table} WHERE imageable_id = %s ORDER BY `highlighted_index` ASC",
                $record_id
            ),
            ARRAY_A
        );

        $record['images'] = [
            'featured'  => null,
            'banner'    => null,
            'evidence'  => [],
            'media_coverage' => []
        ];

        if ($images) {
            foreach ($images as $img) {
                switch (strtolower($img['type'])) {
                    case 'featured':
                        $record['images']['featured'] = $img;
                        break;

                    case 'banner':
                        $record['images']['banner'] = $img;
                        break;

                    case 'evidence':
                        $record['images']['evidence'][] = $img;
                        break;

                    case 'media coverage':
                        $record['images']['media_coverage'][] = $img;
                        break;
                }
            }
        }

        $videos = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$videos_table} WHERE videoable_id = %s",
                $record_id
            ),
            ARRAY_A
        );

        $record['videos'] = $videos ?: [];

        $payment = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$payment_table} WHERE payable_id = %s",
                $record_id
            ),
            ARRAY_A
        );

        $record['payment'] = $payment ?: [];

        return $record;
    }

    function edit_appreciation_section()
    {
        global $wpdb, $common_class;
        $record_id = $_POST['record_id'] ?? null;
        $section = sanitize_text_field($_POST['section']);
        $error_msg = 'Something went wrong.';
        if (!$record_id || !isset($_POST['section'])) {
            wp_send_json_error('Invalid request.');
            return;
        }

        $get_records = $this->get_record_with_holders($record_id);
        $fields = [];
        switch ($section) {
            case 'basicdetails':
                $fields = [
                    'user_id'               => intval($_POST['assigned_user']),
                    'category_fee_id'       => wgt_get_cat_fee_id($_POST['applying_as'], $get_records['country'], 'appreciation'),
                    'estimate_participants' => sanitize_text_field($_POST['estimate_participants'] ?? 0),
                    'approval_status'       => sanitize_text_field($_POST['approval_status'] ?? ''),
                    'cancel_reason'         => sanitize_text_field($_POST['cancel_reason'] ?? ''),
                ];

                if ($get_records['approval_status'] != 'Approved' && $_POST['approval_status'] == 'Approved') {
                    $fields['approved_at'] = current_time('mysql');
                }

                if ($_POST['applying_as'] == 1) {
                    $fields1['holder_name']        = sanitize_text_field($_POST['holder_name'] ?? '');
                    $fields1['holder_parent_name'] = sanitize_text_field($_POST['holder_parent_name'] ?? '');
                    $fields1['dob'] = sanitize_text_field($_POST['dob'] ?? '');
                }

                if ($_POST['applying_as'] == 2) {
                    $fields1['holder_name']        = sanitize_text_field($_POST['holder_name'] ?? '');
                    $fields1['second_holder_name'] = sanitize_text_field($_POST['second_holder_name'] ?? '');
                }
                
                if ($_POST['applying_as'] == 3 || $_POST['applying_as'] == 4) {
                    $fields1['holder_name']        = sanitize_text_field($_POST['holder_name'] ?? '');
                }

                if ($_POST['applying_as'] == 5) {
                    $fields1['company_name'] = sanitize_text_field($_POST['company_name'] ?? '');
                }

                $update  = $wpdb->update($this->record_table, $fields, ['id' => $record_id]);
                $update1 = $this->update_holder_data($record_id, $fields1, 'appreciation');
                break;
            case 'addressdetails':
                $fields = [
                    'address' => sanitize_text_field($_POST['address'] ?? ''),
                    'country' => sanitize_text_field(wgt_get_country_name_by_code(sanitize_text_field($_POST['country']))),
                    'state'   => sanitize_text_field($_POST['rstate'] ?? ''),
                    'city'    => sanitize_text_field($_POST['city'] ?? ''),
                    'zipcode' => sanitize_text_field($_POST['zipcode'] ?? ''),
                    'cc'      => sanitize_text_field($_POST['phone_country_code'] ?? ''),
                    'mobile'  => sanitize_text_field($_POST['phone_number'] ?? ''),
                ];
                $update = $wpdb->update($this->record_table, $fields, ['id' => $record_id]);
                break;

            case 'recorddetails':
                $slug   = $common_class->get_unique_slug($record_id, $this->record_table, sanitize_text_field(! empty($_POST['slug']) ? $_POST['slug'] : 'appreciation'));
                $fields = [
                    'purpose_id'      => intval($_POST['purpose'] ?? 0),
                    'title'           => sanitize_text_field($_POST['title'] ?? ''),
                    'slug'            => $slug,
                    'attempt_title'   => sanitize_text_field($_POST['attempt_title'] ?? ''),
                    'description'     => wp_kses_post(wp_unslash($_POST['description']) ?? ''),
                    'date'            => date('Y-m-d', strtotime($_POST['date'] ?? '')),
                ];
                $update = $wpdb->update($this->record_table, $fields, ['id' => $record_id]);
                break;

            case 'evidenceimages':
                $ids = !empty($_POST['evidence_images']) ? array_filter(array_map('intval', explode(',', $_POST['evidence_images']))) : [];
                $evidence = [];
                if (! empty($ids)) {
                    foreach ($ids as $index => $id) {
                        $evidence[] = [
                            'src'         => $id, // get image URL from ID
                            'highlighted' => 0,                          // or set from POST if needed
                        ];
                    }
                }

                $fields = [
                    'evidence' => $evidence,
                ];
                $update = $this->updateimages($record_id, $fields);
                break;

            case 'mediacoverageimages':
                $ids = !empty($_POST['media_coverage_images']) ? array_filter(array_map('intval', explode(',', $_POST['media_coverage_images']))) : [];
                $media_coverage = [];
                if (! empty($ids)) {
                    foreach ($ids as $index => $id) {
                        $media_coverage[] = [
                            'src'         => $id, // get image URL from ID
                            'highlighted' => 0,                          // or set from POST if needed
                        ];
                    }
                }

                $fields = [
                    'media_coverage' => $media_coverage,
                ];
                $update = $this->updateimages($record_id, $fields);
                break;

            case 'evidencevideos':
                $videos_raw = stripslashes($_POST['evidence_videos'] ?? '[]');
                $videos = json_decode($videos_raw, true) ?: [];
                $update = $this->updatevideos($record_id, $videos);
                break;

            case 'bannerandfeatureimagevideos':
                $fields = [
                    'banner' => [
                        'src' => sanitize_text_field($_POST['banner_image'] ?? ''),
                    ],
                    'featured' => [
                        'src' => sanitize_text_field($_POST['featured_image'] ?? '')
                    ],
                ];
                $update = $this->updateimages($record_id, $fields);
                break;

            case 'concentform':
                $consent_form_id = intval($_POST['consent_form'] ?? 0);
                if ($consent_form_id) {
                    $mime = get_post_mime_type($consent_form_id);

                    if ($mime === 'application/pdf') {
                        $update = $wpdb->update($this->record_table, ['consent_form' => $consent_form_id], ['id' => $record_id]);
                    } else {
                        $error_msg = 'Only PDF files are allowed';
                    }
                } else {
                    $update = $wpdb->update($this->record_table, ['consent_form' => null], ['id' => $record_id]);
                }
                break;

            case 'payment':
                $fields = [
                    'reference_id' => sanitize_text_field($_POST['reference_id'] ?? ''),
                    'receipt_id' => sanitize_text_field($_POST['receipt_id'] ?? ''),
                    'payment_date' => sanitize_text_field($_POST['payment_date'] ?? ''),
                    'amount' => floatval($_POST['amount'] ?? 0),
                    'country' => sanitize_text_field($_POST['payment_country'] ?? ''),
                    'currency' => sanitize_text_field($_POST['currency'] ?? ''),
                    'gateway' => sanitize_text_field($_POST['gateway'] ?? ''),
                    'gateway_ref_id' => sanitize_text_field($_POST['gateway_ref_id'] ?? ''),
                    'status' => sanitize_text_field($_POST['status'] ?? ''),
                    'is_captured' => intval($_POST['is_captured'] ?? 0),
                ];
                break;

            default:
                wp_send_json_error('Invalid section.');
                return;
        }

        if ($update) {
            wp_send_json_success('Super Talented Kid updated successfully.');
        } else {
            wp_send_json_error($error_msg);
        }
    }

    public function updateimages($record_id, $data)
    {
        global $wpdb;

        if (!$this->exists($record_id)) {
            return false;
        }

        $table = $this->image_table;
        $now   = current_time('mysql');
        $return = false;

        // --- Featured image ---
        if (isset($data['featured'])) {
            // Delete old Featured
            $return = $wpdb->delete($table, [
                'imageable_id' => $record_id,
                'type'         => 'Featured'
            ]);

            if (!empty($data['featured']['src'])) {
                // Insert new Featured
                $return = $wpdb->insert(
                    $table,
                    [
                        'id'             => wp_generate_uuid4(),
                        'imageable_type' => 'appreciation',
                        'imageable_id'   => $record_id,
                        'type'           => 'Featured',
                        'src'            => sanitize_text_field($data['featured']['src']),
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]
                );
            }
        }

        // --- Banner image ---
        if (isset($data['banner'])) {
            // Delete old Banner
            $return = $wpdb->delete($table, [
                'imageable_id' => $record_id,
                'type'         => 'Banner'
            ]);

            if (!empty($data['banner']['src'])) {
                // Insert new Banner
                $return = $wpdb->insert(
                    $table,
                    [
                        'id'             => wp_generate_uuid4(),
                        'imageable_type' => 'appreciation',
                        'imageable_id'   => $record_id,
                        'type'           => 'Banner',
                        'src'            => sanitize_text_field($data['banner']['src']),
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]
                );
            }
        }

        // --- Evidence images (multiple) ---
        if (isset($data['evidence'])) {
            // Delete old Evidence
            $return = $wpdb->delete($table, [
                'imageable_id' => $record_id,
                'type'         => 'Evidence'
            ]);

            if (is_array($data['evidence'])) {
                // Insert new Evidence
                foreach ($data['evidence'] as $index => $img) {
                    if (empty($img['src'])) {
                        continue;
                    }

                    $return = $wpdb->insert(
                        $table,
                        [
                            'id'                => wp_generate_uuid4(),
                            'imageable_type'    => 'appreciation',
                            'imageable_id'      => $record_id,
                            'type'              => 'Evidence',
                            'src'               => sanitize_text_field($img['src']),
                            'highlighted_index' => $index,
                            'highlighted'       => !empty($img['highlighted']) ? 1 : 0,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ]
                    );
                }
            }
        }
        // --- Media Coverage images (multiple) ---
        if (isset($data['media_coverage'])) {
            // Delete old Evidence
            $return = $wpdb->delete($table, [
                'imageable_id' => $record_id,
                'type'         => 'Media Coverage'
            ]);

            if (is_array($data['media_coverage'])) {
                // Insert new Evidence
                foreach ($data['media_coverage'] as $index => $img) {
                    if (empty($img['src'])) {
                        continue;
                    }

                    $return = $wpdb->insert(
                        $table,
                        [
                            'id'                => wp_generate_uuid4(),
                            'imageable_type'    => 'appreciation',
                            'imageable_id'      => $record_id,
                            'type'              => 'Media Coverage',
                            'src'               => sanitize_text_field($img['src']),
                            'highlighted_index' => $index,
                            'highlighted'       => !empty($img['highlighted']) ? 1 : 0,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ]
                    );
                }
            }
        }

        return $return;
    }

    public function updatevideos($record_id, $data)
    {
        global $wpdb;

        if (!$this->exists($record_id)) {
            return false;
        }

        $return = false;

        $table = $this->video_table;
        $now   = current_time('mysql');

        // Delete old videos
        $return = $wpdb->delete($table, [
            'videoable_id' => $record_id,
        ]);

        if (!empty($data) && is_array($data)) {
            // Insert new videos
            foreach ($data as $index => $vid) {
                if (empty($vid)) {
                    continue;
                }

                $return = $wpdb->insert(
                    $table,
                    [
                        'id'                => wp_generate_uuid4(),
                        'videoable_type'    => 'appreciation',
                        'videoable_id'      => $record_id,
                        'src'               => $vid,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ]
                );
            }
        }

        return $return;
    }

    function create_payment($record_id, $data)
    {
        global $wpdb, $common_class;

        if (!$this->exists($record_id)) {
            return false;
        }

        $receipt_id = $wpdb->get_var("SELECT MAX(receipt_id) FROM {$this->payment_table}");
        $amount     = $common_class->get_currency_conversion_rate($data['currency']) * $common_class->wgt_get_record_fee($data['category_fee_id'], 'INR');

        $india_gateway = get_option('wgt_india_payment_gateway');
        $other_gateway = get_option('wgt_other_payment_gateway');
        $insert = $wpdb->insert($this->payment_table, [
            'id'                   => wp_generate_uuid4(),
            'receipt_id'           => $receipt_id ? intval($receipt_id) + 1 : 10001,
            'payable_type'         => 'appreciation',
            'payable_id'           => $record_id,
            'gateway'              => $data['currency'] == 'INR' ? $india_gateway : $other_gateway,
            'gateway_reference_id' => NULL,
            'country'              => sanitize_text_field($data['country'] ?? ''),
            'currency'             => sanitize_text_field($data['currency'] ?? ''),
            'currency_symbol'      => sanitize_text_field($data['currency_symbol'] ?? ''),
            'amount'               => $amount,
            'response_data'        => NULL,
            'status'               => 'Unpaid',
            'payment_captured'     => 0,
            'paid_at'              => NULL,
            'created_at'           => current_time('mysql'),
            'updated_at'           => current_time('mysql'),
        ]);

        return $insert ? true : false;
    }
}

$GLOBALS['appreciation_record_manager'] = new AppreciationRecordManager();
