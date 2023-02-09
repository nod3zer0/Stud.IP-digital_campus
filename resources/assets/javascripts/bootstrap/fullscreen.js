STUDIP.ready(() => {
    document.getElementById('fullscreen-on').addEventListener('click', event => {
        event.preventDefault();
        STUDIP.Vue.emit('toggle-compact-navigation', true);
    });

    document.getElementById('fullscreen-off').addEventListener('click', event => {
        event.preventDefault();
        STUDIP.Vue.emit('toggle-compact-navigation', false);
    })

    for (const elem of document.querySelectorAll('#focusmode-on, .fullscreen-trigger')) {
        elem.addEventListener('click', event => {
            event.preventDefault();
            STUDIP.Fullscreen.activate();
        });
    }

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
