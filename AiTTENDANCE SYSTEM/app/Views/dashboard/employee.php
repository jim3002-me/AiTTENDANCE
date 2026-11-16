<?php
$pageTitle = 'My attendance';
$employee = isset($employee) && is_array($employee) ? $employee : [];
$history = isset($employee['history']) && is_array($employee['history']) ? $employee['history'] : [];
require dirname(__DIR__) . '/layout.php';
$isJo = ($employee['employment_type'] ?? '') === 'Job Order';
?>
<section class="dashboard-welcome employee-welcome">
    <div><p class="eyebrow">Employee self-service · <?= Security::escape(date('l, F j, Y')) ?></p><h1>Welcome, <?= Security::escape($employee['first_name'] ?? '') ?>.</h1><p>Your attendance and rendered hours, in one place.</p></div>
    <div class="dashboard-branding employee-branding"><img class="municipality-mark" src="assets/margosatubig-seal.jpg" alt="Seal of the Municipality of Margosatubig, Zamboanga del Sur"><div class="employee-chip"><span class="employee-avatar"><?= Security::escape(strtoupper(substr($employee['first_name'] ?? 'E', 0, 1))) ?></span><span><strong><?= Security::escape($employee['employee_code'] ?? '') ?></strong><small><?= Security::escape($employee['dept_name'] ?? 'Unassigned department') ?></small></span></div></div>
</section>
<section class="employee-badge-section" aria-labelledby="employee-badge-heading">
    <div class="employee-badge-copy"><p class="eyebrow">Personal attendance badge</p><h2 id="employee-badge-heading">Your QR badge</h2><p class="muted">This badge is linked to your employee code and stays the same each time you sign in.</p><strong class="badge-code"><?= Security::escape($employee['employee_code'] ?? '') ?></strong></div>
    <article class="employee-badge-card" id="employee-badge-card">
        <img class="badge-logo" src="assets/margosatubig-seal.jpg" alt="Municipality of Margosatubig seal">
        <p class="eyebrow">Municipality of Margosatubig</p>
        <h3><?= Security::escape(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')) ?></h3>
        <p class="badge-meta"><?= Security::escape($employee['dept_name'] ?? 'Unassigned department') ?></p>
        <div id="employee-qr" class="generated-qr-canvas" data-code="<?= Security::escape($employee['employee_code'] ?? '') ?>"></div>
        <p class="badge-code"><?= Security::escape($employee['employee_code'] ?? '') ?></p>
    </article>
    <div class="employee-badge-actions"><a class="button button-primary" id="employee-qr-download" download>Download badge</a><button class="button button-secondary" id="employee-qr-print" type="button">Print badge</button></div>
    <p id="employee-qr-error" class="notice notice-error" role="alert" hidden></p>
</section>
<section class="metric-row dashboard-metrics employee-metrics" aria-label="My monthly summary">
    <div class="metric"><span>Days recorded this month</span><strong><?= (int) ($employee['month_days'] ?? 0) ?></strong><small><?= (int) ($employee['month_late_days'] ?? 0) ?> late arrival<?= (int) ($employee['month_late_days'] ?? 0) === 1 ? '' : 's' ?></small></div>
    <div class="metric"><span>Hours rendered this month</span><strong><?= number_format((float) ($employee['month_hours'] ?? 0), 2) ?></strong><small>Verified completed shifts</small></div>
    <div class="metric"><span><?= $isJo ? 'Estimated gross this month' : 'Employment type' ?></span><strong class="<?= $isJo ? '' : 'metric-text' ?>"><?= $isJo ? '₱' . number_format((float) ($employee['month_estimated_pay'] ?? 0), 2) : Security::escape($employee['employment_type'] ?? '') ?></strong><small><?= $isJo ? 'Calculated from completed hours' : Security::escape($employee['dept_name'] ?? 'Unassigned department') ?></small></div>
</section>
<?php if ($isJo): ?><section class="employee-rate-strip"><span>Current JO daily rate <strong>₱<?= number_format((float) ($employee['daily_rate'] ?? 0), 2) ?></strong></span><span>Hourly equivalent <strong>₱<?= number_format((float) ($employee['daily_rate'] ?? 0) / 8, 2) ?></strong></span></section><?php endif; ?>
<section class="panel table-panel employee-history"><div class="panel-heading"><div><p class="eyebrow">Personal records</p><h2>Recent attendance</h2></div><span class="muted">Latest 20 days with records</span></div>
    <div class="table-scroll"><table><thead><tr><th>Date</th><th>Time in</th><th>Time out</th><th>Hours</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($history as $record): ?>
        <tr><td><?= Security::escape(date('D, M j, Y', strtotime($record['log_date']))) ?></td><td><?= $record['time_in'] ? Security::escape(date('g:i A', strtotime($record['time_in']))) : '—' ?></td><td><?= $record['time_out'] ? Security::escape(date('g:i A', strtotime($record['time_out']))) : '<span class="muted">In progress</span>' ?></td><td><?= number_format((float) ($record['hours_rendered'] ?? 0), 2) ?></td><td><span class="status-pill <?= $record['status'] === 'Late' ? 'status-late' : 'status-ok' ?>"><?= Security::escape($record['status'] ?? '') ?></span></td></tr>
    <?php endforeach; ?>
    <?php if (!$history): ?><tr><td class="empty-state" colspan="5">No attendance records are available yet.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<script src="assets/vendor/qrcode.min.js" defer></script>
<?php require dirname(__DIR__) . '/footer.php'; ?>