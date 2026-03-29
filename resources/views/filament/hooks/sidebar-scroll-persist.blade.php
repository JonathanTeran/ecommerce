<script>
(function () {
    const KEY = 'filament-sidebar-scroll';

    function getScrollableContainer() {
        // Filament v3: the scrollable element is the sidebar's inner container
        const selectors = [
            '.fi-sidebar-nav',
            'aside.fi-sidebar .overflow-y-auto',
            'aside.fi-sidebar nav',
            '[x-ref="sidebar"] .overflow-y-auto',
            '[x-ref="sidebar"] nav',
            'aside nav',
            '.fi-sidebar',
        ];
        for (const sel of selectors) {
            const el = document.querySelector(sel);
            if (el && el.scrollHeight > el.clientHeight) {
                return el;
            }
        }
        // Fallback: return any sidebar element even if not scrollable yet
        for (const sel of selectors) {
            const el = document.querySelector(sel);
            if (el) {
                return el;
            }
        }
        return null;
    }

    function save() {
        const el = getScrollableContainer();
        if (el) {
            sessionStorage.setItem(KEY, el.scrollTop);
        }
    }

    function restore() {
        const saved = sessionStorage.getItem(KEY);
        if (!saved || saved === '0') return;

        let attempts = 0;
        const tryRestore = () => {
            const el = getScrollableContainer();
            if (el) {
                el.scrollTop = parseInt(saved, 10);
                // Verify it actually scrolled (element might not be rendered yet)
                if (el.scrollTop < parseInt(saved, 10) - 5 && attempts < 20) {
                    attempts++;
                    setTimeout(tryRestore, 50);
                }
            } else if (attempts < 20) {
                attempts++;
                setTimeout(tryRestore, 50);
            }
        };
        tryRestore();
    }

    // Save before SPA navigation
    document.addEventListener('livewire:navigate', save);

    // Also save periodically when user scrolls sidebar
    let scrollTimer;
    document.addEventListener('scroll', function(e) {
        const el = getScrollableContainer();
        if (el && (e.target === el || el.contains(e.target))) {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(save, 100);
        }
    }, true);

    // Restore after SPA navigation completes
    document.addEventListener('livewire:navigated', function() {
        setTimeout(restore, 50);
    });

    // Also restore on initial load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(restore, 100);
        });
    } else {
        setTimeout(restore, 100);
    }
})();
</script>
