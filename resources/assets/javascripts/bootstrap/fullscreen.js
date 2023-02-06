STUDIP.ready(() => {
    $('#fullscreen-on').on('click', event => {
        event.preventDefault();
        STUDIP.Vue.emit('toggle-compact-navigation', true);
    });
    $('#fullscreen-off').on('click', event => {
        event.preventDefault();
        STUDIP.Vue.emit('toggle-compact-navigation', false);
    });
    $('#focusmode-on, .fullscreen-trigger').on('click', event => {
        event.preventDefault();
        STUDIP.Fullscreen.activate();
    });
    // Listen for fullscreen exit, ending focus mode with it.
    document.addEventListener('fullscreenchange', event => {
        if (!document.fullscreenElement) {
            STUDIP.Fullscreen.deactivate();
        }
    });
    // Fullscreen exit on Safari
    document.addEventListener('webkitfullscreenchange', event => {
        if (!document.webkitFullscreenElement) {
            STUDIP.Fullscreen.deactivate();
        }
    });
});
