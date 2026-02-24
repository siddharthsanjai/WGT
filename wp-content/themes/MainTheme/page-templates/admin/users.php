<?php ?>
<div class="wrap">
    <?php include_once get_template_directory() . '/page-templates/admin/tabs.php'; ?>
    <h1>WGT Users</h1>

    <div class="filter-section">
        <form method="get">
            <input type="hidden" name="page" value="wgt">
            <input type="hidden" name="tab" value="users">

            <select name="status_filter">
                <option value="">All Status</option>
                <option value="active" <?php selected(isset($_GET['status_filter']) ? $_GET['status_filter'] : '', 'active'); ?>>Active</option>
                <option value="inactive" <?php selected(isset($_GET['status_filter']) ? $_GET['status_filter'] : '', 'inactive'); ?>>Inactive</option>
            </select>

            <input type="text" name="search" placeholder="Search by name or email" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">

            <button type="submit" class="button">Filter</button>
        </form>
    </div>

    <div class="users-list">
        <?php
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $users_per_page = 20;

        $args = array(
            'number' => $users_per_page,
            'paged' => $paged,
            'orderby' => 'registered',
            'order' => 'DESC',
            'role__in' => ['customer']
        );

        // Apply filters
        if (!empty($_GET['status_filter'])) {
            $args['meta_query'] = array(
                array(
                    'key' => 'status',
                    'value' => sanitize_text_field($_GET['status_filter']),
                    'compare' => '='
                )
            );
        }

        if (!empty($_GET['search'])) {
            $args['search'] = '*' . sanitize_text_field($_GET['search']) . '*';
        }

        $users = new WP_User_Query($args);
        $total_users = $users->get_total();
        $total_pages = ceil($total_users / $users_per_page);

        if ($users->get_results()) {
        ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>SR No</th>
                        <th>Registration ID</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Contact No</th>
                        <th>Date of Birth</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sr_no = ($paged - 1) * $users_per_page + 1;
                    foreach ($users->get_results() as $user) {
                        $registration_id = get_user_meta($user->ID, 'registration_id', true) ?: 'NA';
                        $status = get_user_meta($user->ID, 'status', true) ?: 'NA';
                        $mobile = get_user_meta($user->ID, 'mobile_no', true) ?: 'NA';
                        $dob = get_user_meta($user->ID, 'date_of_birth', true) ?: 'NA';
                        $category = get_user_meta($user->ID, 'category', true) ?: 'NA';
                        $gender = get_user_meta($user->ID, 'gender', true) ?: 'NA';
                        $full_name =  get_user_meta($user->ID, 'first_name', true) . ' ' . get_user_meta($user->ID, 'last_name', true);
                        $registered_on = $user->user_registered ? date('Y-m-d', strtotime($user->user_registered)) : 'Not available';
                    ?>
                        <tr>
                            <td><?php echo esc_html($sr_no++); ?></td>
                            <td><?php echo esc_html($registration_id); ?></td>
                            <td><?php echo esc_html($full_name ?: 'NA'); ?></td>
                            <td><?php echo esc_html($gender); ?></td>
                            <td><?php echo esc_html($user->user_email ?: 'NA'); ?></td>
                            <td><?php echo esc_html($mobile); ?></td>
                            <td><?php echo esc_html($dob); ?></td>
                            <td><?php echo esc_html($category); ?></td>
                            <td><?php echo esc_html($status); ?></td>
                            <td><?php echo esc_html($registered_on); ?></td>
                            <td>
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>" class="button">Edit</a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>

        <?php
            // Pagination
            if ($total_pages > 1) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $paged
                ));
                echo '</div></div>';
            }
        } else {
            echo '<p>No users found.</p>';
        }
        ?>
    </div>
</div>