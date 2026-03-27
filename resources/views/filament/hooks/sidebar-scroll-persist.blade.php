<script>
(function () {
    const KEY = 'filament-sidebar-scroll';

    function getSidebar() {
        return document.querySelector('.fi-sidebar-nav')
            || document.querySelector('[x-ref="sidebar"] nav')
            || document.querySelector('aside nav');
    }

    function save() {
        const el = getSidebar();
        if (el && el.scrollTop > 0) {
            sessionStorage.setItem(KEY, el.scrollTop);
        }
    }

    function restore() {
        const saved = sessionStorage.getItem(KEY);
        if (!saved) return;
        const tryRestore = (attempts) => {
            const el = getSidebar();
            if (el) {
                el.scrollTop = parseInt(saved, 10);
            } else if (attempts < 10) {
                requestAnimationFrame(() => tryRestore(attempts + 1));
            }
        };
        tryRestore(0);
    }

    // Save before SPA navigation
    document.addEventListener('livewire:navigate', save);

    // Restore after SPA navigation completes
    document.addEventListener('livewire:navigated', () => {
        requestAnimationFrame(restore);
    });

    // Also restore on initial load
    document.addEventListener('DOMContentLoaded', () => {
        requestAnimationFrame(restore);
    });
})();
</script>
