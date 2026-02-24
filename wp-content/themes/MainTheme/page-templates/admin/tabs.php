<div class="wgt-tabs">
    <button class="tab-button <?php echo !isset($_GET['tab']) || $_GET['tab'] === 'dashboard' ? 'active' : ''; ?>"
        data-tab="dashboard" onclick="window.location.href = '?page=wgt&tab=dashboard';">Dashboard</button>
    <button class="tab-button <?php echo isset($_GET['tab']) && $_GET['tab'] === 'admin' ? 'active' : ''; ?>"
        data-tab="admin" onclick="window.location.href = '?page=wgt&tab=admin';">Admin</button>
    <button class="tab-button <?php echo isset($_GET['tab']) && $_GET['tab'] === 'certificate-fees' ? 'active' : ''; ?>"
        data-tab="certificate-fees" onclick="window.location.href = '?page=wgt&tab=certificate-fees';">Certificate
        Fees</button>
    <button class="tab-button <?php echo isset($_GET['tab']) && $_GET['tab'] === 'users' ? 'active' : ''; ?>"
        data-tab="users" onclick="window.location.href = '?page=wgt&tab=users';">Users</button>
    <button class="tab-button <?php echo isset($_GET['tab']) && $_GET['tab'] === 'records' ? 'active' : ''; ?>"
        data-tab="records" onclick="window.location.href = '?page=wgt&tab=records';">World Records</button>
    <!-- <button class="tab-button <?php echo isset($_GET['tab']) && $_GET['tab'] === 'apt-women' ? 'active' : ''; ?>"
        data-tab="apt-women" onclick="window.location.href = '?page=wgt&tab=apt-women';">APT Women</button> -->
    <button class="tab-button <?php echo isset($_GET['tab']) && $_GET['tab'] === 'appreciation' ? 'active' : ''; ?>"
        data-tab="appreciation" onclick="window.location.href = '?page=wgt&tab=appreciation';">Appreciation
        Awards</button>
    <button class="tab-button <?php echo isset($_GET['tab']) && $_GET['tab'] === 'payments' ? 'active' : ''; ?>"
        data-tab="payments" onclick="window.location.href = '?page=wgt&tab=payments';">Payments</button>
</div>