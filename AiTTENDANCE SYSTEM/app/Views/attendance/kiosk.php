<?php
$pageTitle = 'Attendance scanner';
require dirname(__DIR__) . '/layout.php';
?>
<section class="kiosk-wrap">
    <div class="kiosk-heading"><div class="kiosk-brand"><img src="assets/margosatubig-seal.jpg" alt="Seal of the Municipality of Margosatubig, Zamboanga del Sur"><div><p class="eyebrow">Municipal Government of Margosatubig</p><h1>Attendance scanner</h1><p class="muted">Scan the employee QR badge to record a time-in or time-out.</p></div></div></div>
    <div class="kiosk-grid">
        <section class="panel scanner-panel">
            <div class="panel-heading"><div><p class="eyebrow">Camera terminal</p><h2>Scan QR badge</h2></div><span class="live-indicator">Ready</span></div>
            <div id="reader" class="qr-reader"></div>
            <div id="scan-result" class="notice" role="status" aria-live="polite" hidden></div>
            <p class="scanner-help">Allow camera access when prompted. The QR should contain the employee code.</p>
        </section>
        <aside class="panel kiosk-note"><span class="note-index">01</span><h2>Secure timekeeping</h2><p>Only active employee codes are accepted. Each scan records the server timestamp and updates the daily attendance register.</p><div class="clock-block"><span>Local time</span><strong id="live-clock">--:--:--</strong></div></aside>
    </div>
</section>
<meta name="csrf-token" content="<?= Security::escape(Security::csrfToken()) ?>">
<script src="https://unpkg.com/html5-qrcode" defer></script>
<?php require dirname(__DIR__) . '/footer.php'; ?>