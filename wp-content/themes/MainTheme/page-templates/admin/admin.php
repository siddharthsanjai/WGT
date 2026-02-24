<?php
// Display success message if role was created
if (isset($_GET['role_created']) && $_GET['role_created'] == '1') {
?>
    <div class="notice notice-success is-dismissible">
        <p>User created successfully!</p>
    </div>
<?php
}

// Display success message if user was deleted
if (isset($_GET['user_deleted']) && $_GET['user_deleted'] == '1') {
?>
    <div class="notice notice-success is-dismissible">
        <p>User deleted successfully!</p>
    </div>
<?php
}
if (isset($_GET['tab']) && $_GET['tab'] == 'admin') { ?>
<div class="wrap">
    <?php include_once get_template_directory() . '/page-templates/admin/tabs.php'; ?>

    <h1>IBR Admin - Roles Management</h1>

    <?php if (wgt_has_edit_access()) { ?>
        <!-- Add New User Button -->
        <div class="add-new-user-section">
            <button type="button" class="button button-primary" id="add-new-user-btn">Add New Role</button>
        </div>
    <?php } ?>

    <!-- User Filters -->
    <div class="filter-section">
        <form method="get">
            <input type="hidden" name="page" value="wgt">
            <input type="hidden" name="tab" value="admin" />
            <select name="role_filter">
                <option value="">All Roles</option>
                <option value="editor" <?php selected(isset($_GET['role_filter']) ? $_GET['role_filter'] : '', 'editor'); ?>>Editor</option>
                <option value="viewer" <?php selected(isset($_GET['role_filter']) ? $_GET['role_filter'] : '', 'viewer'); ?>>Viewer</option>
            </select>

            <select name="status_filter">
                <option value="">All Status</option>
                <option value="active" <?php selected(isset($_GET['status_filter']) ? $_GET['status_filter'] : '', 'active'); ?>>Active</option>
                <option value="inactive" <?php selected(isset($_GET['status_filter']) ? $_GET['status_filter'] : '', 'inactive'); ?>>Inactive</option>
            </select>

            <input class="search" type="text" name="search" placeholder="Search by name or email" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">

            <button type="submit" class="button">Filter</button>
            <a href="?page=wgt&tab=admin" class="button">Reset</a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="users-list">
        <?php
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $users_per_page = 20;

        $args = array(
            'number' => $users_per_page,
            'paged' => $paged,
            'orderby' => 'registered',
            'role__in' => ['editor', 'viewer'],
            'order' => 'DESC'
        );

        // Apply filters
        if (!empty($_GET['role_filter'])) {
            $args['role'] = sanitize_text_field($_GET['role_filter']);
        }

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
            <table class="wp-list-table widefat fixed striped admin-roles">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Mobile</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($users->get_results() as $user) {
                        $first_name = get_user_meta($user->ID, 'first_name', true);
                        $last_name = get_user_meta($user->ID, 'last_name', true);
                        $status = get_user_meta($user->ID, 'status', true);
                        $phone_code = get_user_meta($user->ID, 'phone_code', true);
                        $phone_number = get_user_meta($user->ID, 'phone_number', true);

                    ?>
                        <tr>
                            <td>
                                <img src="<?php echo esc_url(get_user_profile_image($user->ID)); ?>" class="user-avatar" alt="<?php echo esc_attr($user->display_name); ?>">
                                <?php echo esc_html($user->display_name); ?>
                            </td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                            <td>
                                <span class="status-<?php echo esc_attr($status); ?>">
                                    <?php echo esc_html(ucfirst($status)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html('+' . $phone_code . ' ' .  $phone_number); ?></td>
                            <td>
                                <div class="row-actions">
                                    <?php if (wgt_has_edit_access()) { ?>
                                        <span class="edit">
                                            <a href="#" class="edit-user button button-primary" data-user-id="<?php echo $user->ID; ?>" data-nonce="<?php echo wp_create_nonce('wgt_get_user_data'); ?>">Edit</a>
                                        </span>
                                    <?php } ?>
                                    <span class="view">
                                        <a href="#" class="view-user button button-primary" data-user-id="<?php echo $user->ID; ?>" data-nonce="<?php echo wp_create_nonce('wgt_get_user_data'); ?>">View</a>
                                    </span>

                                    <?php if (wgt_has_edit_access()) { ?>
                                        <span class="delete button">
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wgt-admin&action=delete&user=' . $user->ID), 'delete_user_' . $user->ID); ?>"
                                                class="delete-user"
                                                data-user-id="<?php echo $user->ID; ?>"
                                                data-user-name="<?php echo esc_attr($user->display_name); ?>">Delete</a>
                                        </span>
                                    <?php } ?>
                                </div>
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

    <!-- Add New User Modal -->
    <div id="add-user-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Role</h2>
            <form method="post" action="" enctype="multipart/form-data" id="add-user-form">
                <?php wp_nonce_field('wgt_create_role', 'wgt_role_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="name">Name *</label></th>
                        <td>
                            <input type="text" name="name" id="name" class="regular-text" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="email">Email *</label></th>
                        <td>
                            <input type="email" name="email" id="email" class="regular-text" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="mobile">Mobile No. *</label></th>
                        <td>
                            <input type="tel" name="mobile" id="mobile" class="regular-text intl-mobile" required>
                            <input type="hidden" name="country_code" id="country_code">
                            <input type="hidden" name="phone_number" id="phone_number">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="gender">Gender</label></th>
                        <td>
                            <select name="gender" id="gender">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="role">Role *</label></th>
                        <td>
                            <select name="role" id="role" required>
                                <option value="">Select Role</option>
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="status">Status *</label></th>
                        <td>
                            <select name="status" id="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="password">Create Password *</label></th>
                        <td>
                            <input type="password" name="password" id="password" class="regular-text" required>
                            <div class="password-requirements">
                                <p>Password must contain:</p>
                                <ul>
                                    <li id="length">At least 8 characters</li>
                                    <li id="uppercase">One uppercase letter</li>
                                    <li id="lowercase">One lowercase letter</li>
                                    <li id="number">One number</li>
                                    <li id="special">One special character</li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="confirm_password">Confirm Password *</label></th>
                        <td>
                            <input type="password" name="confirm_password" id="confirm_password" class="regular-text" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="profile_photo">Profile Photo</label></th>
                        <td>
                            <input type="hidden" name="profile_photo_id" id="profile_photo_id">
                            <button type="button" id="profile_photo" class="button">Choose Image</button>
                            <div id="profile-preview"></div>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="create_role" class="button button-primary" value="Create User">
                </p>
            </form>
        </div>
    </div>

    <?php if (wgt_has_edit_access()) : ?>
        <!-- Edit User Modal -->
        <div id="edit-user-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Edit Role</h2>
                <form method="post" action="" enctype="multipart/form-data" id="edit-user-form">
                    <?php wp_nonce_field('wgt_edit_user', 'wgt_edit_nonce'); ?>
                    <input type="hidden" name="user_id" id="edit_user_id">

                    <table class="form-table">

                        <tr>
                            <th scope="row"><label for="edit_profile_photo">Profile Photo</label></th>
                            <td>

                                <div id="edit-profile-preview"></div>
                                <input type="hidden" name="profile_photo_id" id="edit_profile_photo_id">
                                <button type="button" id="edit_profile_photo" class="button">Edit Image</button>
                                <div class="error-message" id="edit-profile-photo-error"></div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="edit_name">Name *</label></th>
                            <td>
                                <input type="text" name="name" id="edit_name" class="regular-text" required>
                                <div class="error-message" id="edit-name-error"></div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="edit_email">Email *</label></th>
                            <td>
                                <input type="email" name="email" id="edit_email" class="regular-text" disabled>
                                <div class="error-message" id="edit-email-error"></div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="edit_mobile">Mobile No. *</label></th>
                            <td>
                                <input type="tel" name="mobile" id="edit_mobile" class="regular-text intl-mobile" required>
                                <input type="hidden" name="edit_country_code" id="edit_country_code">
                                <input type="hidden" name="edit_phone_number" id="edit_phone_number">
                                <div class="error-message" id="edit-mobile-error"></div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="edit_gender">Gender</label></th>
                            <td>
                                <select name="gender" id="edit_gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="edit_role">Role *</label></th>
                            <td>
                                <select name="role" id="edit_role" required>
                                    <option value="">Select Role</option>
                                    <option value="editor">Editor</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                                <div class="error-message" id="edit-role-error"></div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="edit_status">Status *</label></th>
                            <td>
                                <select name="status" id="edit_status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="error-message" id="edit-status-error"></div>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" name="edit_user" class="button button-primary" value="Update User">
                    </p>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- View User Modal -->
    <div id="view-user-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>User Details</h2>
            <div id="user-details">
                <table class="form-table">
                    <tr>
                        <th>Profile Photo</th>
                        <td id="view_profile_photo"></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td id="view_name"></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td id="view_email"></td>
                    </tr>
                    <tr>
                        <th>Mobile</th>
                        <td id="view_mobile"></td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td id="view_gender"></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td id="view_role"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="view_status"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 800px;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    .add-new-user-section {
        margin: 20px 0;
    }

    /* Row Actions */
    /* .row-actions {
        visibility: hidden;
    } */

    /* tr:hover .row-actions {
        visibility: visible;
    }

    .row-actions span {
        padding: 0 5px;
    }

    .row-actions span:last-child {
        padding-right: 0;
    }

    .delete-user {
        color: #dc3232;
    }

    .delete-user:hover {
        color: #a00;
    } */

    /* User Avatar */
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        margin-right: 10px;
        vertical-align: middle;
    }

    /* Status Indicators */
    .status-active {
        color: #46b450;
        font-weight: bold;
    }

    .status-inactive {
        color: #dc3232;
        font-weight: bold;
    }

    /* Profile Preview */
    #profile-preview img,
    #edit-profile-preview img {
        max-width: 150px;
        margin-top: 10px;
        border: 1px solid #ddd;
        padding: 5px;
        background: #fff;
    }

    /* Error Messages */
    .error-message {
        color: #dc3232;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    .error {
        border-color: #dc3232 !important;
    }

    .valid {
        border-color: #46b450 !important;
    }

    /* Password Fields */
    #password_fields {
        display: none;
        margin-top: 15px;
        padding: 15px;
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    #password_fields.show {
        display: block;
    }

    .password-requirements {
        margin-top: 10px;
        font-size: 12px;
    }

    .password-requirements ul {
        list-style: none;
        padding-left: 0;
        margin: 5px 0;
    }

    .password-requirements li {
        color: #666;
        margin: 3px 0;
        position: relative;
        padding-left: 20px;
    }

    .password-requirements li:before {
        content: '×';
        position: absolute;
        left: 0;
        color: #dc3232;
    }

    .password-requirements li.valid:before {
        content: '✓';
        color: #46b450;
    }

    /* Validation styles */
    input.error,
    select.error {
        border-color: #dc3232 !important;
    }

    input.valid,
    select.valid {
        border-color: #46b450 !important;
    }

    .error-message {
        color: #dc3232;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .iti {
        position: relative;
        width: 100%;
    }

    .iti__flag-container {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        align-items: center;
        padding-left: 0;
        z-index: 2;
        background-color: #f8f8f8;
        border-right: 1px solid #ddd;
    }

    .iti__selected-flag {
        height: 100%;
        display: flex;
        align-items: center;
        padding: 0 10px;
        background-color: #f8f8f8;
        border-right: 1px solid #ddd;
    }

    .iti__dial-code {
        margin-left: 4px;
        font-weight: bold;
        color: #333;
        white-space: nowrap;
    }

    .iti input[type="tel"] {
        padding-left: 85px !important;
        /* increased to accommodate flag + code */
        height: 42px;
        line-height: 42px;
        font-size: 16px;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .iti__country-list {
        z-index: 99999 !important;
        position: absolute !important;
        background: white;
        max-height: 250px;
        overflow-y: auto;ibr
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #ccc;
        margin-top: 2px;
    }

    .iti--allow-dropdown input,
    .iti--allow-dropdown .iti__flag-container {
        vertical-align: middle;
    }

    /* Optional: Avoid dropdown overflow in modals */
    .iti__country-list {
        overflow-x: hidden;
    }

    .admin-roles .row-actions {
        visibility: visible !important;
        opacity: 1 !important;
        position: static !important;
    }
</style>
