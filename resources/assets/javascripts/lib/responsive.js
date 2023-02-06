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

    isCompactNavigation() {
        const cache = STUDIP.Cache.getInstance('responsive.');
        let result = false;
        if (STUDIP.USER_ID) {
            result = cache.get('fullscreen-mode') ?? false;
        } else {
            cache.remove('fullscreen-mode');
        }
        return result;
    }
};

export default Responsive;
