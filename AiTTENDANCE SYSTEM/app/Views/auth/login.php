<?php
$error = isset($error) && is_string($error) ? $error : null;
require dirname(__DIR__) . '/layout.php';
?>
<section class="login-panel">
    <img class="auth-logo" src="assets/margosatubig-seal.jpg" alt="Seal of the Municipality of Margosatubig, Zamboanga del Sur">
    <p class="eyebrow">Municipal Government of Margosatubig</p>
    <h1>AiTTENDANCE</h1>
    <p class="muted">Human resources and payroll portal</p>
    <?php if ($error): ?><p class="notice notice-error" role="alert"><?= Security::escape($error) ?></p><?php endif; ?>
    <form method="post" action="index.php?action=login" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>">
        <label>Username<input name="username" autocomplete="username" required autofocus></label>
        <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="button button-primary" type="submit">Sign in</button>
    </form>
</section>
<?php require dirname(__DIR__) . '/footer.php'; ?>