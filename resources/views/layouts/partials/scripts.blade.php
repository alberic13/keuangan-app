<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.documentElement;
        const overlay = document.querySelector('.app-sidebar-overlay');
        const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
        const closeButtons = document.querySelectorAll('[data-sidebar-close]');
        const mobileQuery = window.matchMedia('(max-width: 767px)');
        const isMobile = () => mobileQuery.matches;

        const setDesktopState = (state) => {
            root.dataset.sidebarDesktop = state;
            try {
                localStorage.setItem('sidebarDesktopState', state);
            } catch (error) {
                // Ignore storage access issues.
            }
        };

        const setMobileState = (state) => {
            root.dataset.sidebarMobile = state;
        };

        toggleButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (isMobile()) {
                    setMobileState(root.dataset.sidebarMobile === 'open' ? 'closed' : 'open');
                    return;
                }

                setDesktopState(root.dataset.sidebarDesktop === 'closed' ? 'open' : 'closed');
            });
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', () => setMobileState('closed'));
        });

        overlay?.addEventListener('click', () => setMobileState('closed'));

        mobileQuery.addEventListener('change', (event) => {
            if (! event.matches) {
                setMobileState('closed');
            }
        });
    });
</script>
