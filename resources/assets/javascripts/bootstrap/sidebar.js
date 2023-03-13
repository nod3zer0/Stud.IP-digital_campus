// Set correct sidebar height for stickyness
STUDIP.domReady(() => {
    if (!STUDIP.Responsive.isResponsive()) {
        STUDIP.Sidebar.adjustHeight();
    }
});
