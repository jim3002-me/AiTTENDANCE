<?php
$pageTitle = 'Employee DTR';
$employees = isset($employees) && is_array($employees) ? $employees : [];
$report = isset($report) && is_array($report) ? $report : null;
$error = isset($error) && is_string($error) ? $error : null;
$month = isset($month) && is_string($month) ? $month : date('Y-m');
$employeeId = isset($employeeId) ? $employeeId : null;
require dirname(__DIR__) . '/layout.php';
$employee = $report['employee'] ?? [];
$recordsByDate = $report['records_by_date'] ?? [];
$totalHours = 0.0;
$lateDays = 0;
foreach ($recordsByDate as $record) {
    $totalHours += (float) ($record['hours_rendered'] ?? 0);
    $lateDays += ($record['status'] ?? '') === 'Late' ? 1 : 0;
}
?>
<section class="page-heading dtr-controls no-print"><div><p class="eyebrow">Human resources · Printed reports</p><h1>Daily Time Record</h1><p class="muted">Select an employee and month to prepare their DTR for printing.</p></div><?php if ($report): ?><button class="button button-primary" type="button" onclick="window.print()">Print / Save PDF</button><?php endif; ?></section>
<section class="panel form-panel dtr-filter no-print"><form method="get" action="index.php" class="date-filter"><input type="hidden" name="action" value="dtr"><label>Employee<select name="employee_id" required><option value="">Select employee</option><?php foreach ($employees as $row): ?><option value="<?= (int) $row['employee_id'] ?>" <?= (int) $row['employee_id'] === (int) ($employeeId ?? 0) ? 'selected' : '' ?>><?= Security::escape($row['last_name'] . ', ' . $row['first_name'] . ' · ' . $row['employee_code']) ?></option><?php endforeach; ?></select></label><label>Month<input type="month" name="month" value="<?= Security::escape($month) ?>" required></label><button class="button button-primary" type="submit">Generate DTR</button></form></section>
<?php if ($error): ?><p class="notice notice-error no-print" role="alert"><?= Security::escape($error) ?></p><?php endif; ?>
<?php if (!$employees): ?><p class="notice no-print">No active employees are available to report.</p><?php endif; ?>
<?php if ($report): ?>
<article class="dtr-sheet">
    <header class="dtr-header"><img src="assets/margosatubig-seal.jpg" alt="Municipality seal"><div><p>Republic of the Philippines</p><p>Municipality of Margosatubig</p><p>Zamboanga del Sur</p></div></header>
    <h2>DAILY TIME RECORD</h2>
    <p class="dtr-period">For the month of <strong><?= Security::escape($report['month']->format('F Y')) ?></strong></p>
    <div class="dtr-employee"><span>Employee name: <strong><?= Security::escape($employee['first_name'] . ' ' . $employee['last_name']) ?></strong></span><span>Employee code: <strong><?= Security::escape($employee['employee_code']) ?></strong></span><span>Department: <strong><?= Security::escape($employee['dept_name'] ?? 'Unassigned') ?></strong></span></div>
    <p class="dtr-note">Daily time records shown below are the attendance entries saved by the QR attendance system. A blank date means no attendance record was saved for that date.</p>
    <table class="dtr-table"><thead><tr><th>Date</th><th>Day</th><th>Time in</th><th>Time out</th><th>Hours rendered</th><th>Status</th></tr></thead><tbody>
    <?php for ($day = 1; $day <= $report['days']; $day++):
        $date = $report['month']->setDate((int) $report['month']->format('Y'), (int) $report['month']->format('m'), $day);
        $dateKey = $date->format('Y-m-d');
        $record = $recordsByDate[$dateKey] ?? null;
    ?>
        <tr><td><?= $day ?></td><td><?= Security::escape($date->format('D')) ?></td><td><?= $record && $record['time_in'] ? Security::escape(date('g:i A', strtotime($record['time_in']))) : '' ?></td><td><?= $record && $record['time_out'] ? Security::escape(date('g:i A', strtotime($record['time_out']))) : '' ?></td><td><?= $record && (float) $record['hours_rendered'] > 0 ? number_format((float) $record['hours_rendered'], 2) : '' ?></td><td><?= $record ? Security::escape($record['status']) : '' ?></td></tr>
    <?php endfor; ?>
    </tbody><tfoot><tr><th colspan="4">Monthly total</th><th><?= number_format($totalHours, 2) ?> hours</th><th><?= $lateDays ?> late</th></tr></tfoot></table>
    <div class="dtr-signatures"><div><span class="signature-line"></span><strong>Employee signature</strong><small>Certify attendance shown above</small></div><div><span class="signature-line"></span><strong>Authorized HR officer</strong><small>Reviewed and verified</small></div></div>
    <footer class="dtr-footer">Generated from AiTTENDANCE · <?= Security::escape(date('F j, Y')) ?></footer>
</article>
<?php endif; ?>
<?php require dirname(__DIR__) . '/footer.php'; ?>