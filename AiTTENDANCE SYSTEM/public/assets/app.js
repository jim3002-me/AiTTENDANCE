document.addEventListener('DOMContentLoaded', () => {
    const employeeType = document.getElementById('employment-type');
    const rateField = document.getElementById('daily-rate-field');
    if (employeeType && rateField) {
        const rateInput = rateField.querySelector('input');
        const syncRate = () => {
            rateInput.required = true;
            rateInput.min = employeeType.value === 'JO' ? '401' : '0.01';
            rateInput.max = employeeType.value === 'JO' ? '414' : '99999999.99';
        };
        employeeType.addEventListener('change', syncRate);
        syncRate();
    }

    const dialog = document.getElementById('qr-dialog');
    const qrContainer = document.getElementById('qr-canvas');
    if (dialog && qrContainer) {
        document.querySelectorAll('.qr-button').forEach((button) => {
            button.addEventListener('click', () => {
                const error = document.getElementById('qr-error');
                const download = document.getElementById('download-badge');
                document.getElementById('qr-name').textContent = button.dataset.name;
                document.getElementById('qr-code-label').textContent = button.dataset.code;
                error.hidden = true;
                download.removeAttribute('href');
                if (!window.QRCode) {
                    error.textContent = 'The local QR generator did not load. Reload the page and try again.';
                    error.hidden = false;
                    dialog.showModal();
                    return;
                }
                qrContainer.replaceChildren();
                new QRCode(qrContainer, {
                    text: button.dataset.code,
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
                    dialog.showModal();
                    return;
                }
                download.href = qrCanvas.toDataURL('image/png');
                download.download = `${button.dataset.code}-qr-badge.png`;
                dialog.showModal();
            });
        });
        document.getElementById('print-badge').addEventListener('click', () => window.print());
    }

    const employeeQr = document.getElementById('employee-qr');
    if (employeeQr) {
        const error = document.getElementById('employee-qr-error');
        const download = document.getElementById('employee-qr-download');
        const code = employeeQr.dataset.code;
        if (!window.QRCode) {
            error.textContent = 'The local QR generator did not load. Reload the page and try again.';
            error.hidden = false;
        } else {
            try {
                new QRCode(employeeQr, {
                    text: code,
                    width: 240,
                    height: 240,
                    colorDark: '#111111',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
                const qrCanvas = employeeQr.querySelector('canvas');
                if (!qrCanvas) {
                    throw new Error('Canvas QR rendering is unavailable.');
                }
                download.href = qrCanvas.toDataURL('image/png');
                download.download = `${code}-attendance-badge.png`;
            } catch (generationError) {
                error.textContent = 'Your QR badge could not be generated in this browser.';
                error.hidden = false;
            }
        }
        document.getElementById('employee-qr-print').addEventListener('click', () => window.print());
    }

    const accountDialog = document.getElementById('employee-account-dialog');
    if (accountDialog) {
        document.querySelectorAll('.account-button').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('account-employee-id').value = button.dataset.employeeId;
                document.getElementById('account-employee-name').textContent = button.dataset.name;
                accountDialog.showModal();
            });
        });
    }

    const employeeEditDialog = document.getElementById('employee-edit-dialog');
    if (employeeEditDialog) {
        const typeSelect = document.getElementById('edit-employment-type');
        const rateInput = document.getElementById('edit-daily-rate');
        const syncEditRate = () => {
            rateInput.min = typeSelect.value === 'JO' ? '401' : '0.01';
            rateInput.max = typeSelect.value === 'JO' ? '414' : '99999999.99';
        };
        typeSelect.addEventListener('change', syncEditRate);
        document.querySelectorAll('.employee-edit-button').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('edit-employee-id').value = button.dataset.employeeId;
                document.getElementById('edit-employee-name').textContent = button.dataset.name;
                document.getElementById('edit-department').value = button.dataset.department;
                typeSelect.value = button.dataset.type;
                rateInput.value = button.dataset.rate;
                syncEditRate();
                employeeEditDialog.showModal();
            });
        });
    }

    const clock = document.getElementById('live-clock');
    if (clock) {
        const updateClock = () => { clock.textContent = new Date().toLocaleTimeString(); };
        updateClock();
        window.setInterval(updateClock, 1000);
    }

    const reader = document.getElementById('reader');
    if (reader && window.Html5QrcodeScanner) {
        const result = document.getElementById('scan-result');
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        let busy = false;
        const scanner = new Html5QrcodeScanner('reader', { fps: 8, qrbox: { width: 230, height: 230 } }, false);
        scanner.render(async (decodedText) => {
            if (busy) return;
            busy = true;
            try {
                const response = await fetch('index.php?action=scan', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ employee_code: decodedText })
                });
                const data = await response.json();
                result.hidden = false;
                result.className = `notice ${data.status === 'success' ? 'notice-success' : 'notice-error'}`;
                result.textContent = data.status === 'success'
                    ? `${data.employee.name} · ${data.employee.department} · ${data.message} ${data.time}`
                    : data.message;
                window.setTimeout(() => { busy = false; }, data.status === 'success' ? 2500 : 1600);
            } catch (error) {
                result.hidden = false;
                result.className = 'notice notice-error';
                result.textContent = 'Could not reach the attendance service. Check the connection and try again.';
                window.setTimeout(() => { busy = false; }, 2000);
            }
        });
    }
});