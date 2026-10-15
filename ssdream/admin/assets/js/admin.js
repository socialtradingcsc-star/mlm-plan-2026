/**
 * Social Service Dream - Admin Portal Script
 */

document.addEventListener('DOMContentLoaded', function () {
    // Sidebar toggle for smaller viewports
    const toggleBtn = document.getElementById('admin-sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('d-none');
        });
    }

    // Binary Tree Zoom Controls
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
