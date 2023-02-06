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

Sidebar.adjustHeight = () => {
    const display = $('#sidebar').css('display');

    if (display === 'none') {
        $('#sidebar').css('display', 'block');
    }
    const lastWidget = $('.sidebar-widget:last-child');
    if (lastWidget.length > 0) {
        const height = lastWidget.offset().top + lastWidget.height();
        $('#sidebar').css('height', height + 'px');
    }
    if (display === 'none') {
        $('#sidebar').css('display', 'none');
    }
}
export default Sidebar;
