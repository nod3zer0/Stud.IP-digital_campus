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

Sidebar.checkActiveLineHeight = () => {
    $('#sidebar .sidebar-widget-content .widget-links li.active a.active').each(function() {
        var link = $(this);
        var actual_text = link.text();
        link.text('tmp');
        var default_height = link.outerHeight();
        link.text(actual_text);
        var actual_height = link.outerHeight();
        if (actual_height > default_height) { //it is rendered in more lines
            link.css('line-height', '20px');
        }
    });
}
export default Sidebar;
