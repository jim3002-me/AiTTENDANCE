<?php
$pageTitle = 'QR generator';
$employees = isset($employees) && is_array($employees) ? $employees : [];
require dirname(__DIR__) . '/layout.php';
?>
<section class="page-heading"><div><p class="eyebrow">Human resources · Employee tools</p><h1>QR badge generator</h1><p class="muted">Generate a printable, downloadable attendance badge for an active employee.</p></div></section>
<section class="qr-generator-grid">
    <section class="panel qr-generator-form"><div class="panel-heading"><div><p class="eyebrow">Badge details</p><h2>Select employee</h2></div></div>
        <?php if ($employees): ?>
        <form id="qr-generator-form" class="form-stack">
            <label>Employee<select id="qr-employee" required><option value="">Choose an employee</option><?php foreach ($employees as $employee): ?><option value="<?= Security::escape($employee['employee_code']) ?>" data-name="<?= Security::escape($employee['first_name'] . ' ' . $employee['last_name']) ?>" data-department="<?= Security::escape($employee['dept_name'] ?? 'Unassigned') ?>" data-type="<?= Security::escape($employee['employment_type']) ?>"><?= Security::escape($employee['last_name'] . ', ' . $employee['first_name'] . ' · ' . $employee['employee_code']) ?></option><?php endforeach; ?></select></label>
            <button class="button button-primary" type="submit">Generate QR badge</button>
        </form>
        <?php else: ?>
        <p class="empty-state">No active employees are available. Add an employee before generating a QR badge.</p>
        <a class="button button-secondary" href="index.php?action=employees">Open employee directory</a>
        <?php endif; ?>
        <p id="qr-generator-error" class="notice notice-error" role="alert" hidden></p>
    </section>
    <section class="panel qr-preview-panel" aria-live="polite">
        <div class="qr-preview-empty" id="qr-preview-empty"><span class="qr-preview-symbol">QR</span><h2>Your badge preview</h2><p>Select an employee to generate their attendance QR.</p></div>
        <article class="generated-badge" id="generated-badge" hidden>
            <img class="badge-logo" src="assets/margosatubig-seal.jpg" alt="Municipality of Margosatubig seal">
            <p class="eyebrow">Municipality of Margosatubig</p>
            <h2 id="badge-employee-name"></h2>
            <p class="badge-meta" id="badge-employee-details"></p>
            <div id="generated-qr" class="generated-qr-canvas"></div>
            <p class="badge-code" id="badge-employee-code"></p>
        </article>
        <div class="badge-actions" id="badge-actions" hidden>
            <a class="button button-primary" id="qr-download" download>Download PNG</a>
            <button class="button button-secondary" id="qr-print" type="button">Print badge</button>
        </div>
    </section>
</section>
<script src="assets/vendor/qrcode.min.js" defer></script>
<script src="assets/qr-generator.js" defer></script>
<?php require dirname(__DIR__) . '/footer.php'; ?>