/**
 * Social Service Dream - Frontend Application Script
 */

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss Bootstrap alert messages after 6 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            try {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } catch (e) {
                alert.style.display = 'none';
            }
        }, 6000);
    });

    // Password visibility toggle
    const toggleButtons = document.querySelectorAll('.btn-toggle-password');
    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                const isPassword = targetInput.getAttribute('type') === 'password';
                targetInput.setAttribute('type', isPassword ? 'text' : 'password');
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                }
            }
        });
    });

    // Clipboard copy utility for referral links / sponsor IDs
    window.copyToClipboard = function (text, triggerBtn) {
        if (!navigator.clipboard) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showCopiedTooltip(triggerBtn);
            return;
        }

        navigator.clipboard.writeText(text).then(function () {
            showCopiedTooltip(triggerBtn);
        }).catch(function (err) {
            console.error('Copy failed: ', err);
        });
    };

    function showCopiedTooltip(btn) {
        if (!btn) return;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
        btn.classList.add('btn-success');
        setTimeout(function () {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
        }, 2000);
    }
});
