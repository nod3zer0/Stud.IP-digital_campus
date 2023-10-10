const Sidebar = {

    observeSidebar() {
        const options = {
            root: null,
            rootMargin: '0px 0px 35px 0px',
            threshold: 1
        };

        /**
         * Observe if sidebar fits into viewport.
         */
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            const sObserver = new IntersectionObserver(STUDIP.Sidebar.fits, options);
            sObserver.observe(sidebar, options);
        }
    },

    observeFooter() {
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 1
        };

        /**
         * Observe if the footer is visible in viewport.
         */
        const fObserver = new IntersectionObserver(STUDIP.Sidebar.footerVisible, options);
        fObserver.observe(document.getElementById('main-footer'), options);

    },

    reset() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('oversized', 'was-oversized', 'fixed');
            sidebar.style.top = '';
            STUDIP.Sidebar.observeSidebar();
        }
    },

    fits(entries, observer) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            entries.forEach(entry => {
                // Sidebar fits onto current page.
                if (entry.isIntersecting) {
                    sidebar.classList.remove('oversized');
                } else {
                    sidebar.classList.add('oversized', 'was-oversized');
                }
            });
        }
    },

    footerVisible(entries, observer) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            entries.forEach(entry => {
                // Footer is visible on current page.
                if (entry.isIntersecting) {
                    sidebar.classList.remove('no-footer');
                } else {
                    sidebar.classList.add('no-footer');
                }
            });
        }
    }
};

export default Sidebar;
