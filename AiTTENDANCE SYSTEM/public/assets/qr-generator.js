document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('qr-generator-form');
    if (!form) return;

    const employeeSelect = document.getElementById('qr-employee');
    const qrContainer = document.getElementById('generated-qr');
    const error = document.getElementById('qr-generator-error');
    const badge = document.getElementById('generated-badge');
    const actions = document.getElementById('badge-actions');
    const download = document.getElementById('qr-download');

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const selected = employeeSelect.selectedOptions[0];
        const code = employeeSelect.value;
        error.hidden = true;
        if (!code || !selected) return;
        if (!window.QRCode) {
            error.textContent = 'The local QR generator did not load. Reload the page and try again.';
            error.hidden = false;
            return;
        }

        qrContainer.replaceChildren();
        new QRCode(qrContainer, {
            text: code,
            width: 240,
            height: 240,
            colorDark: '#111111',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        const qrCanvas = qrContainer.querySelector('canvas');
        if (!qrCanvas) {
            error.textContent = 'The QR badge could not be generated in this browser.';
            error.hidden = false;
            return;
        }
        document.getElementById('badge-employee-name').textContent = selected.dataset.name;
        document.getElementById('badge-employee-details').textContent = `${selected.dataset.department} · ${selected.dataset.type}`;
        document.getElementById('badge-employee-code').textContent = code;
        download.href = qrCanvas.toDataURL('image/png');
        download.download = `${code}-attendance-badge.png`;
        document.getElementById('qr-preview-empty').hidden = true;
        badge.hidden = false;
        actions.hidden = false;
    });

    document.getElementById('qr-print').addEventListener('click', () => window.print());
});