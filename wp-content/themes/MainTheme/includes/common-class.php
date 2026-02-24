<?php

class CommonClass
{
    public static function get_current_user_id()
    {
        return get_current_user_id();
    }

    function get_currency_code($country_code)
    {
        global $wpdb;

        $currency_code = $wpdb->get_var($wpdb->prepare(
            "SELECT currency FROM {$wpdb->prefix}countries WHERE iso_2 = %s",
            $country_code
        ));
        return $currency_code;
    }

    function get_currency_symbol($country_code)
    {
        global $wpdb;

        $currency_symbol = $wpdb->get_var($wpdb->prepare(
            "SELECT currency_symbol FROM {$wpdb->prefix}countries WHERE iso_2 = %s",
            $country_code
        ));
        return $currency_symbol;
    }

    function get_currency_conversion_rate($to_currency, $from_currency = 'INR')
    {
        global $wpdb;

        $conversion_rate = $wpdb->get_var($wpdb->prepare(
            "SELECT value FROM {$wpdb->prefix}conversions WHERE `from` = %s AND `to` = %s",
            $from_currency,
            $to_currency
        ));
        return $conversion_rate ? floatval($conversion_rate) : 1.0;
    }

    function wgt_get_record_fee($category_fee_id)
    {
        global $wpdb;

        $fee = $wpdb->get_var($wpdb->prepare(
            "SELECT amount FROM {$wpdb->prefix}category_fees WHERE id = %d",
            $category_fee_id
        ));
        return $fee ? floatval($fee) : 0.0;
    }

    function update_seen_status($record_id, $table)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $table;
        $result = $wpdb->update(
            $table_name,
            array('seen' => 1),
            array('id' => $record_id)
        );

        return $result !== false;
    }

    function get_all_application_status()
    {
        $status = array(
            'Under Review',
            'Need Revision',
            'Accepted',
            'Evidence Received',
            'Need More Details',
            'Eligible',
            'Processing Payment',
            'Ready to Approve',
            'Approved',
            'Rejected'
        );
        return $status;
    }

    function email_trigger_conditions($id, $module)
    {
        global $wpdb;
        $conditions = false;
        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}{$module} WHERE id = %d",
            $id
        ), ARRAY_A);

        if ($record) {
            if ($record['status'] == 'Active' && $record['lead_stage'] == 'Interested' && in_array($record['approval_status'], ['Accepted', 'Evidence Received', 'Eligible', 'Approved', 'Rejected'])) {
                $conditions = true;
            }
        }
        return $conditions;
    }

    function get_email_types()
    {
        $email_types = array(
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'eligible' => 'Eligible',
            'paid_confirmation' => 'Paid Confirmation',
            'approved' => 'Approved',
            'certificate_dispatched' => 'Certificate Dispatched'
        );

        return $email_types;
    }

    function trigger_email_event($email_type, $record_id, $module)
    {
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}{$module} WHERE id = %d",
            $record_id
        ));
        // Implement email sending logic here
        // This is a placeholder function
        $emails = WC()->mailer()->get_emails();

        // Check if our email exists
        if (! empty($emails['WC_Email_User_Password_Changed'])) {
            $emails['WC_Email_User_Password_Changed']->trigger($user_id);
        }

        error_log("Triggering email of type '$email_type' for record ID $record_id");
    }

    function display_user_applications_table($user_id, $module)
    {
        $applications = $this->get_all_user_applications_by_module($user_id, $module);
        $slug = 'record';
        if ($module == 'super_talented_kids') {
            $slug = 'super-talented-kid';
        } else if ($module == 'inspiring_humans') {
            $slug = 'inspiring-human';
        } else if ($module == 'apt_women') {
            $slug = 'apt-womens';
        } else if ($module == 'appreciation') {
            $slug = 'appreciation';
        }

        ob_start();

        if (! empty($applications)): ?>

            <div style="width: 80vw; overflow-x:auto;">
                <table id="myTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Application ID</th>
                            <th>Attempt Date</th>
                            <th>Category</th>
                            <th>Country</th>
                            <th>Approval</th>
                            <th>Payment</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sno = 1;
                        foreach ($applications as $app):
                            $category = wgt_get_category_by_fee_id($app['category_fee_id']); ?>
                            <tr>
                                <td><?php echo esc_html($sno++); ?></td>
                                <td><?php echo esc_html($app['application_id']); ?></td>
                                <td><?php echo $app['date'] ? esc_html(date('d M, Y', strtotime($app['date']))) : ''; ?></td>
                                <td><?php echo esc_html($category->name); ?></td>
                                <td><?php echo esc_html($app['country']); ?></td>
                                <td><?php echo esc_html($app['approval_status']); ?></td>
                                <td><?php echo esc_html($app['payment_status']); ?></td>
                                <td><?php echo esc_html(date('d M, Y', strtotime($app['created_at']))); ?></td>
                                <td><?php echo esc_html($app['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(wc_get_account_endpoint_url($slug) . '?id=' . $app['id']); ?>" class="btn btn-danger">View</a>
                                    <?php if ($app['payment_status'] != 'Paid') { ?>
                                        <a href="<?php echo esc_url(site_url() . '/application-fees-payment/?paymentID=' . $app['payment_id']); ?>" class="btn btn-danger">Pay Now</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
        else:
            echo "<br><p>You don't currently have any applications.</p>";
        endif;
        return ob_get_clean();
    }



    function get_all_user_applications_by_module($user_id, $module)
    {
        global $wpdb;
        $records_table = $wpdb->prefix . $module;   // main records table
        $payment_table = $wpdb->prefix . 'payments';

        // Get main record
        $records = $wpdb->get_results(
            $wpdb->prepare("SELECT rt.*,pt.status as payment_status, pt.id as payment_id FROM {$records_table} as rt LEFT JOIN {$payment_table} as pt ON pt.payable_id=rt.id WHERE user_id = %d ORDER BY created_at DESC", $user_id),
            ARRAY_A
        );

        return $records ?: [];
    }

    function get_application_data_by_module($record_id, $module, $by = 'id')
    {
        global $wpdb;

        $records_table = $wpdb->prefix . $module;   // main records table
        $holders_table = $wpdb->prefix . 'holders';    // holder table
        $images_table = $wpdb->prefix . 'images';    // holder table
        $videos_table = $wpdb->prefix . 'videos';   // holder table
        $payment_table = $wpdb->prefix . 'payments';

        // Get main record
        $record = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$records_table} WHERE {$by} = %s", $record_id),
            ARRAY_A
        );

        if (!$record) {
            return null; // record not found
        }

        if ($by == 'slug') {
            $record_id = $record['id'];
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

    function disply_user_application_data($id, $module)
    {
        global $common_class;
        $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
        $record = $common_class->get_application_data_by_module($id, $module); // Your function to get single application
        if (!$record) {
            echo '<p>Application not found.</p>';
            return;
        }

        $user       = get_userdata($record['user_id']);
        $cat        = wgt_get_category_by_fee_id($record['category_fee_id']);
        $holder1 = $record['holders'][0] ?? [];
        $holder2 = $record['holders'][1] ?? [];
        $holder3 = $record['holders'][2] ?? [];
        $consent_form_id          = isset($record['consent_form']) ? intval($record['consent_form']) : 0;
        $payment                  = isset($record['payment']) ? $record['payment'] : [];
        $purpose                  = $this->get_wgt_purposes($record['purpose_id']);
        $slug = 'record';
        if ($module == 'super_talented_kids') {
            $slug = 'super-talented-kid';
        } else if ($module == 'inspiring_humans') {
            $slug = 'inspiring-human';
        } else if ($module == 'apt_women') {
            $slug = 'apt-womens';
        } else if ($module == 'appreciation') {
            $slug = 'appreciation';
        }
        ?>
        <div class="wr-detail-container">
            <a href="<?= esc_url(site_url('/my-account/' . $slug)); ?>" class="btn btn-secondary mb-3">← Back</a>

            <div class="wr-header">
                <h2>Application No #<?= esc_html($record['application_id']); ?></h2>

                <?php if ($record['status'] == 'Active'): ?>
                    <?php if ($record['approval_status'] == 'Under Review'): ?>
                        <div class="alert alert-warning">
                            <i class="dashicons dashicons-info"></i>
                            Your application is <?= esc_html($record['approval_status']); ?>. Please wait for any further update.
                        </div>
                    <?php else: ?>
                        <i class="dashicons dashicons-info"></i>
                        Your application is <?= esc_html($record['approval_status']); ?>
                    <?php endif; ?>
                <?php else: ?>
                    Your application is <?= esc_html($record['status']); ?>
                <?php endif; ?>
            </div>

            <div class="wr-actions mb-3">
                <?php if ($record['approval_status'] === 'Under Review' && $record['status'] === 'Active'): ?>
                    <a href="<?= esc_url(add_query_arg(['id' => $id, 'cancel' => 1], site_url('/my-account/' . $slug))); ?>" class="text-danger">Cancel Application</a>
                <?php endif; ?>
            </div>

            <div class="wr-content d-flex gap-3">
                <div class="wr-tabs" style="min-width: 200px;">
                    <ul>
                        <li><a href="#basic-details">Basic Details</a></li>
                        <li><a href="#address-details">Address Details</a></li>
                        <li><a href="#record-details">Record Details</a></li>
                        <li><a href="#consent-form">Consent Form</a></li>
                        <li><a href="#payment-details">Payment Details</a></li>
                    </ul>
                </div>

                <div class="wr-details flex-fill">
                    <div id="basic-details" class="tab-content">
                        <table class="table table-bordered">
                            <tr>
                                <th>Status</th>
                                <td><?= esc_html($record['status']); ?></td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td><?= esc_html($cat->name); ?></td>
                            </tr>
                            <?php if (!empty($holder1['name'])): ?>
                                <tr>
                                    <th>Record Holder</th>
                                    <td><?= esc_attr($holder1['name'] ?? ''); ?></td>
                                </tr>
                            <?php endif ?>
                            <?php if (!empty($holder1['dob'])): ?>
                                <tr>
                                    <th>Record Holder DOB</th>
                                    <td>
                                        <?= esc_html(date('d-M-Y', strtotime($holder1['dob']))); ?>
                                        <?php
                                        $dob = new DateTime($holder1['dob']);
                                        $today = new DateTime();
                                        $age = $today->diff($dob)->y;
                                        if ($age < 16):
                                        ?>
                                            <span class="text-info small">A consent form is required for age below 16 year.</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($holder1['parent_name'])): ?>
                                <tr>
                                    <th>Record Holder Parent</th>
                                    <td><?= esc_attr($holder1['parent_name'] ?? ''); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($holder2['name'])): ?>
                                <tr>
                                    <th>Record Holder 2</th>
                                    <td><?= esc_attr($holder2['name'] ?? ''); ?></td>
                                </tr>
                            <?php endif ?>
                            <tr>
                                <th>Approval Status</th>
                                <td><?= esc_html($record['approval_status']); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div id="address-details" class="tab-content" style="display:none;">
                        <table class="table table-bordered">
                            <tr>
                                <th>Address</th>
                                <td><?= esc_html($record['address']); ?></td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td><?= esc_html($record['country']); ?></td>
                            </tr>
                            <tr>
                                <th>State</th>
                                <td><?= esc_attr($record['state'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>City</th>
                                <td><?= esc_attr($record['city']); ?></td>
                            </tr>
                            <tr>
                                <th>ZipCode</th>
                                <td><?= esc_attr($record['zipcode'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Mobile No</th>
                                <td>+<?= esc_html($record['cc'] . $record['mobile']); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div id="record-details" class="tab-content" style="display:none;">
                        <table class="table table-bordered">
                            <tr>
                                <th>Purpose</th>
                                <td><?= esc_html(($purpose) ? $purpose['name'] : ''); ?></td>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <td><?= esc_html($record['title'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td><?= esc_attr($record['description'] ?? ''); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div id="consent-form" class="tab-content" style="display:none;">
                        <table class="table table-bordered">
                            <tr>
                                <th>Consent Form</th>
                                <td><?php if (! empty($consent_form_id)): ?>
                                        <a href="<?= esc_attr(wp_get_attachment_url($consent_form_id)); ?>"><?php echo esc_html($record['consent_form_name']); ?></a>
                                    <?php else: ?>
                                        <span class="text-danger">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div id="payment-details" class="tab-content" style="display:none;">
                        <table class="table table-bordered">
                            <tr>
                                <th>Reference Id</th>
                                <td><?= esc_html($payment['id'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Receipt Id</th>
                                <td><?= esc_html($payment['receipt_id']); ?></td>
                            </tr>
                            <tr>
                                <th>Payment Date</th>
                                <td><?= esc_attr(($payment['paid_at']) ? date('d-M-Y', strtotime($payment['paid_at'])) : 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Payable Amount</th>
                                <td><?= esc_attr($payment['currency_symbol'] . $payment['amount']); ?></td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td><?= esc_attr($payment['country'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Payment Currency</th>
                                <td><?= esc_html($payment['currency']); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><?= esc_html($payment['status']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .wr-detail-container .wr-header {
                background: #1e3a66;
                color: #fff;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 15px;
            }

            .wr-detail-container .alert-warning {
                background: #fff3cd;
                padding: 10px;
                border-radius: 4px;
                margin-top: 10px;
            }

            .wr-content {
                display: flex;
                gap: 20px;
            }

            .wr-tabs ul {
                list-style: none;
                padding: 0;
            }

            .wr-tabs ul li {
                margin-bottom: 10px;
            }

            .wr-tabs ul li a {
                text-decoration: none;
                color: #1e3a66;
                font-weight: bold;
            }

            .wr-details table th {
                width: 200px;
                background: #f7f7f7;
            }

            .wr-tabs a.active {
                font-weight: bold;
                color: #0073aa;
            }

            .wr-tabs a {
                display: block;
                padding: 5px 10px;
                margin-bottom: 3px;
                text-decoration: none;
                color: #333;
            }
        </style>
<?php
    }

    function get_wgt_purposes($id = '')
    {
        global $wpdb;
        if ($id) {
            return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}purposes WHERE id = $id", ARRAY_A);
        }
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}purposes", ARRAY_A);
    }

    function wgt_get_explore_records($module, $args = []) {
        global $wpdb;

        // Defaults
        $defaults = [
            'activity' => '',
            'syear'     => '',
            'search'   => '',
            'paged'    => 1,
            'per_page' => 20,
        ];
        $args = wp_parse_args($args, $defaults);
        $where = [
            "a.approval_status = 'Approved'",
            "a.status = 'Active'",
            "a.slug IS NOT NULL",
            "a.slug != ''"
        ];

        $query_args = [];

        $args['cat_module'] = str_replace('_', '-', $module);

        // Activity filter
        if (!empty($args['activity'])) {
            $category_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}categories WHERE module = %s AND `index` = %d",
                $args['cat_module'],
                $args['activity']
            ));

            if ($category_id) {
                $category_fee_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}category_fees WHERE category_id = %d",
                    $category_id
                ));

                if (!empty($category_fee_ids)) {
                    // Build placeholders safely
                    $placeholders = implode(',', array_fill(0, count($category_fee_ids), '%d'));
                    $where[] = "a.category_fee_id IN ($placeholders)";
                    $query_args = array_merge($query_args, $category_fee_ids);
                }
            }
        }

        // Year filter
        if (!empty($args['syear'])) {
            // Use prepared query parameters
            $where[] = "(YEAR(a.date) = %d OR YEAR(a.created_at) = %d)";
            $query_args[] = (int)$args['syear'];
            $query_args[] = (int)$args['syear'];
        }

        // Search filter
        if (!empty($args['search'])) {
            $where[] = "(a.title LIKE %s OR a.description LIKE %s)";
            $search_like = '%' . $wpdb->esc_like($args['search']) . '%';
            $query_args[] = $search_like;
            $query_args[] = $search_like;
        }

        // Build WHERE SQL
        $where_sql = '';
        if (!empty($where)) {
            $where_sql = "WHERE " . implode(' AND ', $where);
        }

        // Pagination
        $offset = ($args['paged'] - 1) * $args['per_page'];

        // Total count
        $total_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}{$module} a $where_sql";
        if (!empty($query_args)) {
            $total = $wpdb->get_var($wpdb->prepare($total_sql, ...$query_args));
        } else {
            $total = $wpdb->get_var($total_sql);
        }

        // Results SQL
        $results_sql = "SELECT a.* FROM {$wpdb->prefix}{$module} a $where_sql ORDER BY a.created_at DESC LIMIT %d OFFSET %d";

        // Merge query args with pagination
        $final_args = array_merge($query_args, [$args['per_page'], $offset]);

        // Execute query safely
        if (!empty($final_args)) {
            $results = $wpdb->get_results($wpdb->prepare($results_sql, ...$final_args));
        } else {
            // No dynamic placeholders
            $results = $wpdb->get_results($wpdb->prepare($results_sql, $args['per_page'], $offset));
        }

        return [
            'results'  => $results,
            'total'    => (int)$total,
            'per_page' => (int)$args['per_page'],
            'paged'    => (int)$args['paged'],
            'pages'    => $args['per_page'] > 0 ? ceil($total / $args['per_page']) : 1,
        ];
    }

    function wgt_get_random_records($module, $limit = 4, $sort='RAND()')
    {
        global $wpdb;

        $table_records = $wpdb->prefix . $module;
        $table_images  = $wpdb->prefix . 'images';

        // Fetch random records for a module with approval status
        $records = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.id, r.title, r.slug, r.description, r.created_at, img.src AS feature_image, r.category_fee_id, r.city, r.state, r.country
                FROM {$table_records} r
                LEFT JOIN {$table_images} img
                    ON img.imageable_id = r.id AND img.type = 'Featured'
                WHERE r.approval_status = %s AND r.slug != ''
                ORDER BY {$sort}
                LIMIT %d",
                'approved',
                $limit
            ),
            ARRAY_A
        );

        return $records ?: [];
    }

    function get_image_url($src)
    {
        $upload_dir = wp_upload_dir();

        if (is_numeric($src)) {
            // If $src is an attachment ID
            $url = wp_get_attachment_url($src);
        } else {
            // If $src is a filename in /uploads/record-images/
            $url = trailingslashit($upload_dir['baseurl']) . 'record-images/' . $src;
        }

        return $url;
    }

    function get_unique_slug($record_id, $table, $slug)
    {
        global $wpdb;
        $slug = sanitize_title($slug);
        // Check if slug exists
        $original_slug = $slug;
        $counter = 1;

        while ($wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE slug = %s AND id != %s",
                $slug,
                $record_id
            )
        ) > 0) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }

    function update_record_status($id, $module)
    {
        global $wpdb;
        $update = $wpdb->update(
            $wpdb->prefix . $module,
            ['status' => 'Cancelled'],
            ['id' => $id],
            ['%s'], // format for "status"
            ['%s']  // format for "id" (change to '%d' if it's always numeric)
        );
        return $update;
    }
}

$GLOBALS['common_class'] = new CommonClass();
