<?php
$pageTitle = 'Set up administrator';
$error = isset($error) && is_string($error) ? $error : null;
require dirname(__DIR__) . '/layout.php';
?>
<section class="login-panel setup-panel">
    <img class="auth-logo" src="assets/margosatubig-seal.jpg" alt="Seal of the Municipality of Margosatubig, Zamboanga del Sur">
    <p class="eyebrow">First-time setup</p>
    <h1>Create HR account</h1>
    <p class="muted">Set up the first administrator to manage employees, attendance, and JO payroll.</p>
    <?php if ($error): ?><p class="notice notice-error" role="alert"><?= Security::escape($error) ?></p><?php endif; ?>
    <form method="post" action="index.php?action=setup" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>">
        <label>Username<input name="username" minlength="3" maxlength="80" autocomplete="username" required autofocus></label>
        <label>Password<input type="password" name="password" minlength="12" autocomplete="new-password" required><small>Use at least 12 characters.</small></label>
        <label>Confirm password<input type="password" name="password_confirmation" minlength="12" autocomplete="new-password" required></label>
        <button class="button button-primary" type="submit">Create HR account</button>
    </form>
</section>
<?php require dirname(__DIR__) . '/footer.php'; ?>