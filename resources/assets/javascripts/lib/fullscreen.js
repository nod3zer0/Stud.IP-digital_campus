const Fullscreen = {
    activate() {
        STUDIP.ActionMenu.closeAll();
        if (document.documentElement.classList.contains('fullscreen-mode')) {
            const cache = STUDIP.Cache.getInstance('responsive.');
            cache.set('was-compact-navigation', true);
        }
        STUDIP.Vue.emit('switch-focus-mode', true);
        document.documentElement.classList.remove('fullscreen-mode');
        document.body.classList.add('consuming_mode');
        if (document.body.requestFullscreen) {
            document.body.requestFullscreen({ hide: true });
        } else if (document.body.webkitRequestFullscreen) { /* Safari */
            document.body.webkitRequestFullscreen({ hide: true });
        }
    },
    deactivate() {
        STUDIP.Vue.emit('switch-focus-mode', false);
        document.body.classList.remove('consuming_mode');
        const cache = STUDIP.Cache.getInstance('responsive.');
        if (cache.get('was-compact-navigation')) {
            STUDIP.Vue.emit('toggle-compact-navigation', true);
        }
        cache.remove('was-compact-navigation');
    }
};

export default Fullscreen;
