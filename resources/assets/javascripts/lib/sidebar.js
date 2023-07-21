import Scroll from './scroll.js';

const Sidebar = {
    open () {
        this.toggle(true);
    },
    close () {
        this.toggle(false);
    },
    toggle (visible = null) {
        visible = visible ?? !$('#sidebar').hasClass('visible-sidebar');

        // Hide navigation
        $('#responsive-toggle').prop('checked', false);
        $('#responsive-navigation').removeClass('visible');

        $('#sidebar').toggleClass('visible-sidebar', visible);
    }
};

export default Sidebar;
