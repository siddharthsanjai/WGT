<div class="filter-section">
    <select id="date-filter">
        <option value="today">Today</option>
        <option value="week">This Week</option>
        <option value="month">This Month</option>
        <option value="year">This Year</option>
        <option value="custom">Custom Range</option>
    </select>
    <div class="input-group" id="custom-date-range" style="display: none;">
        <input type="text" id="start-date" placeholder="Start Date">
        <input type="text" id="end-date" placeholder="End Date">
        <input type="submit" class="button button-info" id="apply-date-filter" value="Apply">
        <input type="hidden" id="dashboard_stats_nonce" value="<?php echo wp_create_nonce('dashboard_stats_nonce'); ?>">
    </div>
</div>

<div class="stats-grid">
</div>

<div class="chart-container" style="display: none;">
    <canvas id="revenue-chart"></canvas>
</div>

<!-- Include Flatpickr CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>