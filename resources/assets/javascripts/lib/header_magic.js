import NavigationShrinker from './navigation_shrinker.js';
import Scroll from './scroll.js';

let fold;
let was_below_the_fold = false;

const scroll = function(scrolltop) {
    var is_below_the_fold = scrolltop > fold,
        menu;
    if (is_below_the_fold !== was_below_the_fold) {
        $('body').toggleClass('fixed', is_below_the_fold);

        menu = $('#navigation-level-1-items').remove();
        if (is_below_the_fold) {
            menu.append(
                $('.action-menu-list li', menu)
                    .remove()
                    .addClass('from-action-menu')
            );
            menu.appendTo('#responsive-menu');
        } else {
            $('.action-menu-list', menu).append(
                $('.from-action-menu', menu)
                    .remove()
                    .removeClass('from-action-menu')
            );
            menu.prependTo('#navigation-level-1');

            NavigationShrinker();

            $('#navigation-level-1-items-toggle').prop('checked', false);
        }

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
