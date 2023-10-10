STUDIP.ready(() => {
    // Apply sidebar magic only on admin/courses
    if (
        document.body.id === 'admin-courses-index'
        && !document.documentElement.classList.contains('responsive-display')
    ) {
        STUDIP.Sidebar.observeFooter();
        STUDIP.Sidebar.observeSidebar();

        document.defaultView.addEventListener('resize', () => {
            STUDIP.Sidebar.reset();
        });
    }

});
