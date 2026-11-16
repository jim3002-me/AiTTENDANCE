<?php
$pageTitle = 'Home';
$summary = isset($summary) && is_array($summary) ? $summary : [];
$recentLogs = isset($summary['recent_logs']) && is_array($summary['recent_logs']) ? $summary['recent_logs'] : [];
require dirname(__DIR__) . '/layout.php';
?>
<section class="dashboard-welcome">
    <div><div class="admin-clock admin-clock-heading"><span>Current time</span><strong id="live-clock" aria-live="off"><?= Security::escape(date('g:i:s A')) ?></strong></div><p class="eyebrow">Management overview · <?= Security::escape(date('l, F j, Y')) ?></p><h1>Good day, <?= Security::escape($_SESSION['user']['username'] ?? 'Administrator') ?>.</h1><p>Here is today's workforce activity at a glance.</p></div>
    <div class="dashboard-branding"><img class="municipality-mark" src="assets/margosatubig-seal.jpg" alt="Seal of the Municipality of Margosatubig, Zamboanga del Sur"><a class="button button-primary" href="index.php?action=kiosk" target="_blank" rel="noopener">Open attendance scanner</a></div>
</section>
<section class="metric-row dashboard-metrics" aria-label="Workforce overview">
    <div class="metric"><span>Active employees</span><strong><?= (int) ($summary['employee_count'] ?? 0) ?></strong><small>Across <?= (int) ($summary['departments'] ?? 0) ?> departments</small></div>
    <div class="metric"><span>Recorded today</span><strong><?= (int) ($summary['recorded_today'] ?? 0) ?></strong><small><?= (int) ($summary['on_shift'] ?? 0) ?> currently on shift</small></div>
    <div class="metric"><span>Late arrivals</span><strong><?= (int) ($summary['late_today'] ?? 0) ?></strong><small>Today's attendance records</small></div>
    <div class="metric"><span>Job Order staff</span><strong><?= (int) ($summary['jo_count'] ?? 0) ?></strong><small>Payroll reporting enabled</small></div>
</section>
<section class="dashboard-grid">
    <div class="panel table-panel dashboard-activity"><div class="panel-heading"><div><p class="eyebrow">Live attendance</p><h2>Today's activity</h2></div><a class="text-link" href="index.php?action=attendance-list">Full register <span aria-hidden="true">→</span></a></div>
        <div class="table-scroll"><table><thead><tr><th>Employee</th><th>Department</th><th>Time</th><th>State</th></tr></thead><tbody>
        <?php foreach ($recentLogs as $row): ?>
            <tr><td><?= Security::escape($row['full_name'] ?? '') ?><small class="table-subtitle"><?= Security::escape($row['employee_code'] ?? '') ?></small></td><td><?= Security::escape($row['dept_name'] ?? 'Unassigned') ?></td><td><?= Security::escape(date('g:i A', strtotime($row['time_out'] ?: $row['time_in']))) ?></td><td><span class="status-pill <?= $row['time_out'] ? 'status-ok' : 'status-live' ?>"><?= $row['time_out'] ? 'Completed' : 'On shift' ?></span></td></tr>
        <?php endforeach; ?>
        <?php if (!$recentLogs): ?><tr><td class="empty-state" colspan="4">No attendance recorded today yet.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <aside class="dashboard-side">
        <section class="panel shortcut-panel"><p class="eyebrow">Workspace</p><h2>Quick access</h2>
            <?php if (($_SESSION['user']['role'] ?? '') === 'HR'): ?><a href="index.php?action=employees"><span class="shortcut-icon">01</span><span><strong>Employee directory</strong><small>Profiles and portal accounts</small></span><b>→</b></a><a href="index.php?action=qr-generator"><span class="shortcut-icon shortcut-blue">QR</span><span><strong>QR badge generator</strong><small>Print or download employee badges</small></span><b>→</b></a><?php endif; ?>
            <?php if (($_SESSION['user']['role'] ?? '') === 'HR'): ?><a href="index.php?action=dtr"><span class="shortcut-icon shortcut-ochre">DTR</span><span><strong>Print employee DTR</strong><small>Monthly attendance record</small></span><b>→</b></a><?php endif; ?>
            <?php if (in_array($_SESSION['user']['role'] ?? '', ['HR', 'Payroll'], true)): ?><a href="index.php?action=payroll"><span class="shortcut-icon shortcut-ochre">02</span><span><strong>JO payroll</strong><small>Hours, rates and CSV reports</small></span><b>→</b></a><?php endif; ?>
            <a href="index.php?action=attendance-list"><span class="shortcut-icon shortcut-blue">03</span><span><strong>Attendance register</strong><small>Review today's time logs</small></span><b>→</b></a>
        </section>
        <section class="dashboard-callout"><span>TIMEKEEPING</span><h2>One clear view of today's workforce.</h2><p>Records are updated as employees scan their QR badges.</p></section>
    </aside>
</section>
<?php require dirname(__DIR__) . '/footer.php'; ?>