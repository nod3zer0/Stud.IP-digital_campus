import eventBus from '../lib/event-bus.ts';

STUDIP.ready(() => {
    // Manually nudge sidebar under main header.
    STUDIP.Sidebar.place();
    STUDIP.Sidebar.observeBody();
    STUDIP.Sidebar.observeFooter();
    STUDIP.Sidebar.observeSidebar();

    document.defaultView.addEventListener('resize',() => {
        STUDIP.Sidebar.reset();
    });

});
