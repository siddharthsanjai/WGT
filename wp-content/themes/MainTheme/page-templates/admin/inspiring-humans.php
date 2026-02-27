<?php
if (!defined('ABSPATH')) exit;

global $wpdb, $common_class;
$active_tab = $_GET['tab'] ?? 'inspiring-humans';
echo '<div class="wrap"><h1>Inspiring Humans</h1>';

if ($active_tab === 'inspiring-humans') {
    $ih_list_table = new Ih_List_Table();
    $ih_list_table->prepare_items();

    // Filters + Search UI
?>
    <form method="get" id="wgt-filter-form" class="wgt-modern-filters">
        <input type="hidden" name="page" value="wgt" />
        <input type="hidden" name="tab" value="inspiring-humans" />

        <div class="wgt-filters-grid">
            <div class="wgt-filter">
                <label>Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="Active" <?php selected($_GET['status'] ?? '', 'Active'); ?>>Active</option>
                    <option value="Inactive" <?php selected($_GET['status'] ?? '', 'Inactive'); ?>>InActive</option>
                </select>
            </div>

            <div class="wgt-filter">
                <label>Category</label>
                <select name="category">
                    <option value="">All</option>
                    <option value="1"<?php selected($_GET['category'] ?? '', '1'); ?>>Solo</option>
                </select>
            </div>

            <div class="wgt-filter">
                <label>Country</label>
                <select name="country">
                    <option value="">All</option>
                    <?php
                    if (function_exists('WC')) {
                        $countries = WC()->countries->get_countries();
                        foreach ($countries as $code => $name) {
                            echo '<option value="' . esc_attr($code) . '" ' . selected($code, $_GET['country'], false) . '>' . esc_html($name) . '</option>';
                        }
                    } else {
                        echo '<option value="">WooCommerce not available</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="wgt-filter">
                <label>Approval</label>
                <select name="approval">
                    <option value="">All</option>
                    <?php 
                    $statuses = $common_class->get_all_application_status();
                    foreach ($statuses as $status) {
                        echo '<option value="' . esc_attr($status) . '" ' . selected($_GET['approval'] ?? '', $status, false) . '>' . esc_html($status) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="wgt-filter">
                <label>Attempt Date</label>
                <div class="wgt-date-range">
                    <input type="date" name="attempt_from" value="<?= esc_attr($_GET['attempt_from'] ?? '') ?>" placeholder="From">
                    <input type="date" name="attempt_to" value="<?= esc_attr($_GET['attempt_to'] ?? '') ?>" placeholder="To">
                </div>
            </div>

            <div class="wgt-filter">
                <label>Application Created Date</label>
                <div class="wgt-date-range">
                    <input type="date" name="created_from" value="<?= esc_attr($_GET['created_from'] ?? '') ?>" placeholder="From">
                    <input type="date" name="created_to" value="<?= esc_attr($_GET['created_to'] ?? '') ?>" placeholder="To">
                </div>
            </div>
        </div>

        <div class="wgt-search-wrap">
            <input type="text" name="s" value="<?= esc_attr($_GET['s'] ?? '') ?>" placeholder="Search Application ID, User Reg ID or Email">
            <a href="<?php echo esc_url(remove_query_arg(array(
                'status','category','country','approval','attempt_from','attempt_to','created_from','created_to','s'
            ))); ?>" class="btn btn-danger">
                Clear Filters
            </a>
        </div>
    </form>


<?php
    // Scrollable wrapper for table
    echo '<div class="wgt-table-wrapper wgt-records-table" style="overflow-x: auto;">';
    $ih_list_table->display();
    echo '</div>';
}
echo '</div>';
?>