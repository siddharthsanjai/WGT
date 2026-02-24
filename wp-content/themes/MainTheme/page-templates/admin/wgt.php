<?php
// Load tabs navigation
include_once get_template_directory() . '/page-templates/admin/tabs.php';

// Get active tab
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>

<div class="wrap wgt-dashboard" style="padding-top: 10px ;">
    <div class="tab-content">

        <?php if ($active_tab === 'dashboard') : ?>
            <div id="dashboard" class="tab-pane active">
                <?php include_once get_template_directory() . '/page-templates/admin/dashboard.php'; ?>
            </div>

        <?php elseif ($active_tab === 'admin') : ?>
            <div id="admin" class="tab-pane active">
                <?php include_once get_template_directory() . '/page-templates/admin/admin.php'; ?>
            </div>

        <?php elseif ($active_tab === 'users') : ?>
            <div id="users" class="tab-pane active">
                <?php include_once get_template_directory() . '/page-templates/admin/users.php'; ?>
            </div>

        <?php elseif ($active_tab === 'certificate-fees') : ?>
            <div id="certificate-fees" class="tab-pane active">
                <?php include_once get_template_directory() . '/page-templates/admin/certificate-fees.php'; ?>
            </div>

        <?php elseif ($active_tab === 'records') : ?>
            <div id="records" class="tab-pane active">
                <?php
                require_once get_stylesheet_directory() . '/includes/backend/class-records-list-table.php';
                require_once get_stylesheet_directory() . '/includes/backend/class-records-participants-list-table.php';

                if (isset($_GET['id'])) {
                    if (isset($_GET['action']) && $_GET['action'] === 'participants') {
                        include_once get_template_directory() . '/page-templates/admin/record-paticipants.php';
                    } else {
                        include_once get_template_directory() . '/page-templates/admin/edit-records.php';
                    }
                } else {
                    include_once get_template_directory() . '/page-templates/admin/records.php';
                }
                ?>
            </div>

        <?php elseif ($active_tab === 'stk') : ?>
            <div id="stk" class="tab-pane active">
                <?php
                require_once get_stylesheet_directory() . '/includes/backend/class-stk-list-table.php';

                if (isset($_GET['id'])) {
                    include_once get_template_directory() . '/page-templates/admin/edit-stk.php';
                } else {
                    include_once get_template_directory() . '/page-templates/admin/stk.php';
                }
                ?>
            </div>

        <?php elseif ($active_tab === 'apt-women') : ?>
            <div id="apt-women" class="tab-pane active">
                <?php
                require_once get_stylesheet_directory() . '/includes/backend/class-aptw-list-table.php';

                if (isset($_GET['id'])) {
                    include_once get_template_directory() . '/page-templates/admin/edit-apt-women.php';
                } else {
                    include_once get_template_directory() . '/page-templates/admin/apt-women.php';
                }
                ?>
            </div>

        <?php elseif ($active_tab === 'inspiring-humans') : ?>
            <div id="inspiring-humans" class="tab-pane active">
                <?php
                require_once get_stylesheet_directory() . '/includes/backend/class-ih-list-table.php';

                if (isset($_GET['id'])) {
                    include_once get_template_directory() . '/page-templates/admin/edit-inspiring-humans.php';
                } else {
                    include_once get_template_directory() . '/page-templates/admin/inspiring-humans.php';
                }
                ?>
            </div>

        <?php elseif ($active_tab === 'appreciation') : ?>
            <div id="appreciation" class="tab-pane active">
                <?php
                require_once get_stylesheet_directory() . '/includes/backend/class-appreciation-list-table.php';
                require_once get_stylesheet_directory() . '/includes/backend/class-records-participants-list-table.php';

                if (isset($_GET['id'])) {
                    if (isset($_GET['action']) && $_GET['action'] === 'participants') {
                        include_once get_template_directory() . '/page-templates/admin/record-paticipants.php';
                    } else {
                        include_once get_template_directory() . '/page-templates/admin/edit-appreciation.php';
                    }
                } else {
                    include_once get_template_directory() . '/page-templates/admin/appreciation.php';
                }
                ?>
            </div>

        <?php elseif ($active_tab === 'payments') : ?>
            <div id="payments" class="tab-pane active">
                <?php
                require_once get_stylesheet_directory() . '/includes/backend/class-payments-list-table.php';
                include_once get_template_directory() . '/page-templates/admin/payments.php';
                ?>
            </div>
        <?php endif; ?>

    </div>
</div>
