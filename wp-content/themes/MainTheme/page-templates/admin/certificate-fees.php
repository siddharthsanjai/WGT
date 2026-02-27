<?php
global $wpdb, $store_currency;
$table = $wpdb->prefix . 'certificates';

// filter
$filter_module = isset($_GET['filter_module']) ? sanitize_text_field($_GET['filter_module']) : '';

$query = "SELECT * FROM $table WHERE active = '1'";
if ($filter_module) {
    $query .= $wpdb->prepare(" AND module = %s", $filter_module);
}
$query .= " ORDER BY created_at ASC";
$results = $wpdb->get_results($query);
?>
<div class="wrap">
    <?php include_once get_template_directory() . '/page-templates/admin/tabs.php'; ?>
    <h1>WGT Certificate Fees</h1>

    <form method="get" class="search-box" style="margin-bottom: 10px;">
        <input type="hidden" name="page" value="wgt" />
        <input type="hidden" name="tab" value="certificate-fees" />
        <label for="filter_module" class="screen-reader-text">Filter by Module:</label>
        <input
            type="search"
            id="filter_module"
            name="filter_module"
            value="<?php echo esc_attr($filter_module); ?>"
            placeholder="Filter by Module" />
        <input type="submit" class="button" value="Filter" />
        <a href="?page=wgt&tab=certificate-fees" class="button">Reset</a>
    </form>

    <table class="widefat striped mt-3">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Module</th>
                <th>Applier</th>
                <th>Delivery Country</th>
                <th>Fees (€)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($results) {
                $i = 1;
                foreach ($results as $row) {
                    echo "<tr>
                        <td>{$i}</td>
                        <td>" . esc_html($row->module) . "</td>
                        <td>" . esc_html($row->applier) . "</td>
                        <td>" . esc_html($row->country) . "</td>
                        <td>
                            <span class='fee-display-{$row->id}'>" . esc_html(number_format($row->fees, 2)) . " " . $store_currency . ((wgt_has_edit_access()) ? "
                                <a href='javascript:void(0);' class='edit-fee' data-id='{$row->id}'>
                                    <span class='dashicons dashicons-edit'></span>
                                </a>" : "") . "
                            </span>
                            <span class='fee-edit-{$row->id}' style='display:none;'>
                                <input type='number' step='0.01' value='" . esc_attr($row->fees) . "' class='fee-input-{$row->id}' style='width:80px;'>
                                <button class='button button-small save-fee' data-id='{$row->id}'>✔</button>
                                <button class='button button-small cancel-fee' data-id='{$row->id}'>✖</button>
                            </span>
                        </td>
                    </tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='6'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</div>