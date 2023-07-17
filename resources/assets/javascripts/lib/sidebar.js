const Sidebar = {

    place() {
        const header = document.getElementById('main-header');
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.style.top =
                header.offsetTop + header.offsetHeight + 'px';
        }
    },

    observeSidebar() {
        const options = {
            root: null,
            rootMargin: '0px',
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

    observeBody() {
        const sidebar = document.getElementById('sidebar');
        /**
         * Observe body for class changes. If "fixed" is added or removed, we are in scroll mode
         * where the top navigation is removed or visible again.
         */
        const mObserver = new MutationObserver(mutations => {
            for (const mutation of mutations) {
                if ((!mutation.oldValue || mutation.oldValue.indexOf('fixed') === -1)
                    && mutation.target.classList.contains('fixed')) {
                    sidebar.style.top = '';
                    sidebar.classList.add('fixed');
                } else if (mutation.oldValue && mutation.oldValue.indexOf('fixed') !== -1
                    && !mutation.target.classList.contains('fixed')) {
                    STUDIP.Sidebar.reset();
                }
            }
        });

        // Observe body for class changes.
        mObserver.observe(document.body, {
            attributes: true,
            attributeOldValue : true,
            attributeFilter: ['class']
        });
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
            STUDIP.Sidebar.place();
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
