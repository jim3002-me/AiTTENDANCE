<?php
$pageTitle = 'Attendance';
$logs = isset($logs) && is_array($logs) ? $logs : [];
require dirname(__DIR__) . '/layout.php';
$presentCount = count($logs);
$lateCount = count(array_filter($logs, static fn(array $row): bool => $row['status'] === 'Late'));
?>
<section class="page-heading"><div><p class="eyebrow">Human resources</p><h1>Attendance register</h1><p class="muted">Today's time records and employee check-ins.</p></div><a class="button button-primary" href="index.php?action=kiosk" target="_blank" rel="noopener">Open scanner</a></section>
<section class="metric-row" aria-label="Today's attendance summary">
    <div class="metric"><span>Recorded today</span><strong><?= $presentCount ?></strong></div>
    <div class="metric"><span>Late arrivals</span><strong><?= $lateCount ?></strong></div>
    <div class="metric"><span>Currently on shift</span><strong><?= count(array_filter($logs, static fn(array $row): bool => empty($row['time_out']))) ?></strong></div>
</section>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Live record</p><h2>Today's attendance</h2></div><button class="button button-secondary" onclick="location.reload()" type="button">Refresh</button></div>
    <div class="table-scroll"><table><thead><tr><th>Employee</th><th>Code</th><th>Department</th><th>Time in</th><th>Time out</th><th>Hours</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($logs as $row): ?>
        <tr><td><?= Security::escape($row['full_name']) ?></td><td><?= Security::escape($row['employee_code']) ?></td><td><?= Security::escape($row['dept_name'] ?? 'Unassigned') ?></td><td><?= Security::escape(date('g:i A', strtotime($row['time_in']))) ?></td><td><?= $row['time_out'] ? Security::escape(date('g:i A', strtotime($row['time_out']))) : '<span class="muted">On shift</span>' ?></td><td><?= $row['hours_rendered'] === null ? '—' : number_format((float) $row['hours_rendered'], 2) ?></td><td><span class="status-pill <?= $row['status'] === 'Late' ? 'status-late' : 'status-ok' ?>"><?= Security::escape($row['status']) ?></span></td></tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?><tr><td class="empty-state" colspan="7">No attendance records have been recorded today.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php require dirname(__DIR__) . '/footer.php'; ?>