<?php
$pageTitle = 'Account not linked';
require dirname(__DIR__) . '/layout.php';
?>
<section class="unlinked-panel"><p class="eyebrow">Employee self-service</p><h1>Your account needs an employee link.</h1><p class="muted">Ask HR to connect your login to your active employee profile. Your attendance data will appear here after it is linked.</p><form method="post" action="index.php?action=logout"><input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>"><button class="button button-secondary" type="submit">Sign out</button></form></section>
<?php require dirname(__DIR__) . '/footer.php'; ?>