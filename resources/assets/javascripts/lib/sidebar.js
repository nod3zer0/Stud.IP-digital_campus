const Sidebar = {

    place() {
        const header = document.getElementById('main-header');
        document.getElementById('sidebar').style.top =
            header.offsetTop + header.offsetHeight + 'px';
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
        const sObserver = new IntersectionObserver(STUDIP.Sidebar.fits, options);
        sObserver.observe(document.getElementById('sidebar'));
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
                    sidebar.classList.add('fixed');
                    sidebar.style.top = '';
                } else if (mutation.oldValue && mutation.oldValue.indexOf('fixed') !== -1
                    && !mutation.target.classList.contains('fixed')) {
                    sidebar.classList.remove('fixed');
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
        fObserver.observe(document.getElementById('main-footer'));

    },

    reset() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('oversized', 'adjusted', 'fixed');
            sidebar.style.top = '';
        }
        STUDIP.Sidebar.observe();
    },

    fits(entries, observer) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            entries.forEach(entry => {
                // Sidebar fits onto current page.
                if (entry.isIntersecting) {
                    sidebar.classList.remove('oversized');
                } else {
                    sidebar.classList.add('oversized', 'adjusted');
                }
            });
        }
    },

    footerVisible(entries, observer) {
        const sidebar = document.getElementById('sidebar');
        entries.forEach(entry => {
            // Footer is visible on current page.
            if (entry.isIntersecting) {
                if (sidebar.classList.contains('no-footer')) {
                    sidebar.classList.remove('no-footer');
                }
            } else {
                if (!sidebar.classList.contains('no-footer')) {
                    sidebar.classList.add('no-footer');
                }
            }
        });
    }
};

export default Sidebar;
