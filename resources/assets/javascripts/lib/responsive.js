import HeaderMagic from './header_magic.js';
import Sidebar from './sidebar.js';

const Responsive = {
    media_query: window.matchMedia('(max-width: 767px)'),

    setResponsiveDisplay (state = true) {
        $('html').toggleClass('responsive-display', state);

        if (state) {
            HeaderMagic.disable();
        } else {
            HeaderMagic.enable();
        }
    },

    engage () {
        Responsive.setResponsiveDisplay(Responsive.isResponsive());

        Responsive.media_query.addEventListener('change', () => {
            Responsive.setResponsiveDisplay(Responsive.isResponsive());
        });
    },

    isResponsive() {
        return Responsive.media_query.matches;
    },

    isFullscreen() {
        const cache = STUDIP.Cache.getInstance('responsive.');

        return cache.get('fullscreen-mode') ?? false;
    }
};

export default Responsive;
