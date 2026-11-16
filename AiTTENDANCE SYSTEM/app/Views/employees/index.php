<?php
$pageTitle = 'Employees';
$error = isset($error) && is_string($error) ? $error : null;
$success = isset($success) && is_string($success) ? $success : null;
$departments = isset($departments) && is_array($departments) ? $departments : [];
$employees = isset($employees) && is_array($employees) ? $employees : [];
require dirname(__DIR__) . '/layout.php';
?>
<section class="page-heading">
    <div><p class="eyebrow">Human resources</p><h1>Employee masterlist</h1><p class="muted">Manage employee departments, employment types, and daily salary rates.</p></div>
    <a class="button button-secondary" href="index.php?action=qr-generator">Generate QR badges</a>
</section>
<?php if ($error): ?><p class="notice notice-error" role="alert"><?= Security::escape($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="notice notice-success" role="status"><?= Security::escape($success) ?></p><?php endif; ?>
<section class="panel form-panel">
    <div class="panel-heading"><div><p class="eyebrow">New record</p><h2>Add employee</h2></div></div>
    <form method="post" action="index.php?action=employees" class="employee-form">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>">
        <label>Employee code<input name="employee_code" maxlength="50" required></label>
        <label>First name<input name="first_name" maxlength="100" required></label>
        <label>Last name<input name="last_name" maxlength="100" required></label>
        <label>Employment type<select name="employment_type" id="employment-type"><option value="Permanent">Permanent</option><option value="JO">Job Order</option></select></label>
        <label>Department<select name="department_id" required><option value="">Select department</option><?php foreach ($departments as $department): ?><option value="<?= (int) $department['department_id'] ?>"><?= Security::escape($department['dept_name']) ?></option><?php endforeach; ?></select></label>
        <label id="daily-rate-field">Daily salary / rate (₱)<input name="daily_rate" type="number" min="0.01" max="99999999.99" step="0.01" value="401.00" required></label>
        <button class="button button-primary" type="submit">Add employee</button>
    </form>
</section>
<section class="panel table-panel">
    <div class="panel-heading"><div><p class="eyebrow">Directory</p><h2>Employees <span class="count">/ <?= count($employees) ?></span></h2></div></div>
    <div class="table-scroll"><table>
        <thead><tr><th>Employee</th><th>Code</th><th>Department</th><th>Type</th><th>Daily salary / rate</th><th>QR badge</th><th>Portal login</th><th>Details</th></tr></thead>
        <tbody>
        <?php foreach ($employees as $employee): ?>
            <tr>
                <td><?= Security::escape($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                <td><?= Security::escape($employee['employee_code']) ?></td>
                <td><?= Security::escape($employee['dept_name'] ?? 'Unassigned') ?></td>
                <td><?= Security::escape($employee['employment_type']) ?></td>
                <td><?= $employee['daily_rate'] === null ? '—' : '₱' . number_format((float) $employee['daily_rate'], 2) ?></td>
                <td><button class="button button-quiet qr-button" type="button" data-code="<?= Security::escape($employee['employee_code']) ?>" data-name="<?= Security::escape($employee['first_name'] . ' ' . $employee['last_name']) ?>">Show badge</button></td>
                <td>
                    <?php if ($employee['has_login']): ?>
                        <span class="status-pill <?= $employee['login_active'] ? 'status-ok' : 'status-late' ?>"><?= $employee['login_active'] ? 'Enabled' : 'Disabled' ?></span>
                        <form method="post" action="index.php?action=employee-account-status" class="account-status-form">
                            <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>">
                            <input type="hidden" name="employee_id" value="<?= (int) $employee['employee_id'] ?>">
                            <input type="hidden" name="status" value="<?= $employee['login_active'] ? 'disable' : 'enable' ?>">
                            <button class="button button-quiet" type="submit"><?= $employee['login_active'] ? 'Disable login' : 'Enable login' ?></button>
                        </form>
                    <?php else: ?>
                        <button class="button button-quiet account-button" type="button" data-employee-id="<?= (int) $employee['employee_id'] ?>" data-name="<?= Security::escape($employee['first_name'] . ' ' . $employee['last_name']) ?>">Create login</button>
                    <?php endif; ?>
                </td>
                <td><button class="button button-quiet employee-edit-button" type="button" data-employee-id="<?= (int) $employee['employee_id'] ?>" data-name="<?= Security::escape($employee['first_name'] . ' ' . $employee['last_name']) ?>" data-department="<?= (int) $employee['department_id'] ?>" data-type="<?= $employee['employment_type'] === 'Job Order' ? 'JO' : 'Permanent' ?>" data-rate="<?= Security::escape((string) $employee['daily_rate']) ?>">Edit</button></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$employees): ?><tr><td class="empty-state" colspan="8">No employees have been added.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</section>
<dialog id="qr-dialog" class="qr-dialog">
    <form method="dialog"><button class="dialog-close" aria-label="Close">×</button></form>
    <img class="badge-logo" src="assets/margosatubig-seal.jpg" alt="Municipality of Margosatubig">
    <p class="eyebrow">Municipality of Margosatubig</p><h2 id="qr-name">Employee badge</h2>
    <div id="qr-canvas" class="generated-qr-canvas"></div><p id="qr-code-label" class="muted"></p>
    <div class="badge-actions"><a id="download-badge" class="button button-primary" download>Download PNG</a><button id="print-badge" class="button button-secondary" type="button">Print badge</button></div>
    <p id="qr-error" class="notice notice-error" role="alert" hidden></p>
</dialog>
<dialog id="employee-account-dialog" class="account-dialog">
    <form method="dialog"><button class="dialog-close" aria-label="Close">×</button></form>
    <p class="eyebrow">Employee self-service</p><h2>Create portal login</h2><p id="account-employee-name" class="muted"></p>
    <form method="post" action="index.php?action=employee-account" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>"><input id="account-employee-id" type="hidden" name="employee_id">
        <label>Username<input name="username" minlength="3" maxlength="80" autocomplete="off" required></label>
        <label>Temporary password<input name="password" type="password" minlength="12" autocomplete="new-password" required><small>At least 12 characters. Share it privately.</small></label>
        <button class="button button-primary" type="submit">Create login</button>
    </form>
</dialog>
<dialog id="employee-edit-dialog" class="employee-edit-dialog">
    <form method="dialog"><button class="dialog-close" aria-label="Close">×</button></form>
    <p class="eyebrow">Employee record</p><h2>Edit employee details</h2><p id="edit-employee-name" class="muted"></p>
    <form method="post" action="index.php?action=employee-update" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>"><input id="edit-employee-id" type="hidden" name="employee_id">
        <label>Department<select id="edit-department" name="department_id" required><?php foreach ($departments as $department): ?><option value="<?= (int) $department['department_id'] ?>"><?= Security::escape($department['dept_name']) ?></option><?php endforeach; ?></select></label>
        <label>Employment type<select id="edit-employment-type" name="employment_type"><option value="Permanent">Permanent</option><option value="JO">Job Order</option></select></label>
        <label id="edit-daily-rate-label">Daily salary / rate (₱)<input id="edit-daily-rate" name="daily_rate" type="number" min="0.01" max="99999999.99" step="0.01" required><small id="edit-rate-help">JO rates must be ₱401.00 to ₱414.00 per day.</small></label>
        <button class="button button-primary" type="submit">Save employee details</button>
    </form>
</dialog>
<script src="assets/vendor/qrcode.min.js" defer></script>
<?php require dirname(__DIR__) . '/footer.php'; ?>