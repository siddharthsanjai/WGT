<?php
$payments_table = new IBR_Payments_List_Table();
$payments_table->prepare_items();
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Payments', 'textdomain'); ?></h1>

    <form method="get" action="">
        <!-- Preserve the tab parameter -->
        <input type="hidden" name="tab" value="payments" />
        <input type="hidden" name="page" value="ibr" />
        <!-- Search box -->
        <p class="search-box">
            <label class="screen-reader-text" for="payment-search-input"><?php _e('Search Payments:', 'textdomain'); ?></label>
            <input type="search" id="payment-search-input" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" />
            <input type="submit" class="button" value="<?php _e('Search Payments', 'textdomain'); ?>" />
        </p>

        <!-- Display the table -->
        <?php $payments_table->display(); ?>
    </form>
</div>
