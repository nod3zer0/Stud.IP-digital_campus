import NavigationShrinker from './navigation_shrinker.js';
import Scroll from './scroll.js';

let fold;
let was_below_the_fold = false;

const scroll = function(scrolltop) {
    var is_below_the_fold = scrolltop > fold,
        menu;
    if (is_below_the_fold !== was_below_the_fold) {
        $('body').toggleClass('fixed', is_below_the_fold);

        was_below_the_fold = is_below_the_fold;
    }
};

const HeaderMagic = {
    enable() {
        fold = $('#navigation-level-1').height();
        Scroll.addHandler('header', scroll);
    },
    disable() {
        Scroll.removeHandler('header');
        $('body').removeClass('fixed');
    }
};

export default HeaderMagic;
