STUDIP.domReady(() => {
    // Test if the header is actually present
    if ($('#top-bar').length > 0) {
        STUDIP.HeaderMagic.enable();
    }
});
