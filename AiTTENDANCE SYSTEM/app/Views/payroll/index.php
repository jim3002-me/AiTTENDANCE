<?php
$pageTitle = 'JO Payroll';
$from = isset($from) && is_string($from) ? $from : date('Y-m-01');
$to = isset($to) && is_string($to) ? $to : date('Y-m-d');
$validDates = isset($validDates) && is_bool($validDates) ? $validDates : true;
$rows = isset($rows) && is_array($rows) ? $rows : [];
require dirname(__DIR__) . '/layout.php';
$grossTotal = array_sum(array_map(static fn(array $row): float => (float) $row['gross_pay'], $rows));
?>
<section class="page-heading"><div><p class="eyebrow">Payroll</p><h1>Job Order summary</h1><p class="muted">Gross earnings are based on verified rendered hours and the saved daily rate.</p></div></section>
<?php if (!$validDates): ?><p class="notice notice-error" role="alert">Choose a valid date range.</p><?php endif; ?>
<section class="panel form-panel"><form method="get" action="index.php" class="date-filter"><input type="hidden" name="action" value="payroll"><label>From<input type="date" name="from" value="<?= Security::escape($from) ?>" required></label><label>To<input type="date" name="to" value="<?= Security::escape($to) ?>" required></label><button class="button button-primary" type="submit">Run report</button><?php if ($validDates): ?><a class="button button-secondary" href="index.php?action=payroll&amp;from=<?= rawurlencode($from) ?>&amp;to=<?= rawurlencode($to) ?>&amp;format=csv">Export CSV</a><?php endif; ?></form></section>
<section class="metric-row"><div class="metric"><span>JO workers in report</span><strong><?= count($rows) ?></strong></div><div class="metric"><span>Gross payroll</span><strong>₱<?= number_format($grossTotal, 2) ?></strong></div><div class="metric"><span>Hourly formula</span><strong class="metric-formula">Daily rate ÷ 8</strong></div></section>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow"><?= Security::escape(date('M j', strtotime($from))) ?> – <?= Security::escape(date('M j, Y', strtotime($to))) ?></p><h2>Rendered hours and gross pay</h2></div></div><div class="table-scroll"><table><thead><tr><th>Employee</th><th>Department</th><th>Daily rate</th><th>Hourly rate</th><th>Hours rendered</th><th>Gross pay</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= Security::escape($row['first_name'] . ' ' . $row['last_name']) ?><small class="table-subtitle"><?= Security::escape($row['employee_code']) ?></small></td><td><?= Security::escape($row['dept_name'] ?? 'Unassigned') ?></td><td>₱<?= number_format((float) $row['daily_rate'], 2) ?></td><td>₱<?= number_format((float) $row['daily_rate'] / 8, 2) ?></td><td><?= number_format((float) $row['total_hours'], 2) ?></td><td class="money">₱<?= number_format((float) $row['gross_pay'], 2) ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td class="empty-state" colspan="6">No completed JO attendance records fall within this period.</td></tr><?php endif; ?></tbody><tfoot><tr><th colspan="5">Gross total</th><th class="money">₱<?= number_format($grossTotal, 2) ?></th></tr></tfoot></table></div></section>
<?php require dirname(__DIR__) . '/footer.php'; ?>