/**
 * Social Service Dream - User Dashboard Script
 */

document.addEventListener('DOMContentLoaded', function () {
    // Quick copy referral link
    const copyBtn = document.getElementById('btn-copy-ref');
    const refInput = document.getElementById('ref-link-input');

    if (copyBtn && refInput) {
        copyBtn.addEventListener('click', function () {
            refInput.select();
            refInput.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(refInput.value).then(function () {
                const original = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                copyBtn.classList.replace('btn-primary', 'btn-success');
                setTimeout(function () {
                    copyBtn.innerHTML = original;
                    copyBtn.classList.replace('btn-success', 'btn-primary');
                }, 2000);
            });
        });
    }

    // Sidebar toggle for mobile devices
    const toggleBtn = document.getElementById('user-sidebar-toggle');
    const sidebar = document.querySelector('.user-sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('d-none');
        });
    }

    // Binary Tree Zoom Controls (Part 4)
    const zoomInBtn = document.getElementById('btn-tree-zoom-in');
    const zoomOutBtn = document.getElementById('btn-tree-zoom-out');
    const resetBtn = document.getElementById('btn-tree-reset');
    const viewport = document.getElementById('tree-viewport');

    if (viewport) {
        let currentZoom = 1;
        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', function () {
                if (currentZoom < 1.6) {
                    currentZoom += 0.15;
                    viewport.style.transform = `scale(${currentZoom})`;
                }
            });
        }
        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', function () {
                if (currentZoom > 0.5) {
                    currentZoom -= 0.15;
                    viewport.style.transform = `scale(${currentZoom})`;
                }
            });
        }
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                currentZoom = 1;
                viewport.style.transform = 'scale(1)';
            });
        }
    }
});
