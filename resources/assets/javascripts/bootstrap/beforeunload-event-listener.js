STUDIP.domReady(() => {
    // Before-unload event listener.
    window.addEventListener('beforeunload', (e) => {
        STUDIP.eventBus.emit('studip:beforeunload', e);
    }, {capture: true});
});
