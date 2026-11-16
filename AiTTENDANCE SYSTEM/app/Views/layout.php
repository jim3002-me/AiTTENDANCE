<?php
$pageTitle = $pageTitle ?? 'AiTTENDANCE';
$currentUser = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Security::escape($pageTitle) ?> | AiTTENDANCE</title>
    <link rel="stylesheet" href="assets/app.css">
    <link rel="stylesheet" href="assets/dashboard.css">
    <link rel="stylesheet" href="assets/account.css">
    <link rel="stylesheet" href="assets/branding.css">
    <link rel="stylesheet" href="assets/employee-badge.css">
    <link rel="stylesheet" href="assets/dtr.css">
</head>
<body class="municipal-building-background">
<?php if ($currentUser): ?>
<header class="topbar">
    <a class="brand" href="index.php?action=dashboard"><img class="brand-logo" src="assets/margosatubig-seal.jpg" alt=""><span>AiTTENDANCE<small>Margosatubig Municipal HR</small></span></a>
    <nav class="main-nav" aria-label="Main navigation">
        <a href="index.php?action=dashboard">Home</a>
        <?php if ($currentUser['role'] !== 'Employee'): ?><a href="index.php?action=attendance-list">Attendance</a><?php endif; ?>
        <?php if ($currentUser['role'] === 'HR'): ?><a href="index.php?action=employees">Employees</a><a href="index.php?action=qr-generator">QR generator</a><a href="index.php?action=dtr">Print DTR</a><?php endif; ?>
        <?php if (in_array($currentUser['role'], ['HR', 'Payroll'], true)): ?><a href="index.php?action=payroll">JO Payroll</a><?php endif; ?>
        <?php if ($currentUser['role'] !== 'Employee'): ?><a href="index.php?action=kiosk" target="_blank" rel="noopener">Open scanner</a><?php endif; ?>
    </nav>
    <div class="user-menu"><span><?= Security::escape($currentUser['username']) ?><small><?= Security::escape($currentUser['role']) ?></small></span>
        <form method="post" action="index.php?action=logout"><input type="hidden" name="csrf_token" value="<?= Security::escape(Security::csrfToken()) ?>"><button class="button button-quiet" type="submit">Sign out</button></form>
    </div>
</header>
<?php endif; ?>
<main class="page-shell">