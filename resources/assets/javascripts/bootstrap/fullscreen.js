STUDIP.domReady(function () {
    $('.fullscreen-toggle').click(() => STUDIP.Fullscreen.toggle());

    if (sessionStorage.getItem('studip-fullscreen') == 'on' && $('.fullscreen-toggle').length > 0) {
        STUDIP.Fullscreen.enter(true);
    }
}, true);
