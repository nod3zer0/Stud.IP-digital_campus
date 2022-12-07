function heightChangeHandler() {
    var curr_height = $(document).height();
    if (doc_height !== curr_height) {
        doc_height = curr_height;
        $(document.body).trigger('sticky_kit:recalc');
    }
}

// Handle dynamic content
if (window.MutationObserver !== undefined) {
    // Attach mutation observer to #content and trigger it on
    // changes to class and style attributes (which affect the height
    // of the content). Trigger a recalculation of the sticky kit when
    // a mutation occurs so the sidebar will
    $(document).ready(function() {
        if ($('#content').length === 0) {
            return;
        }
        var target = $('#content').get(0),
            stickyObserver = new window.MutationObserver(function() {
                window.requestAnimationFrame(function() {
                    $(document.body).trigger('sticky_kit:recalc');
                });
            });
        stickyObserver.observe(target, {
            attributes: true,
            attributeFilter: ['style', 'class'],
            characterData: true,
            childList: true,
            subtree: true
        });
    });
} else {
    // Stores document height (we will need this to check for changes)
    var doc_height;

    STUDIP.domReady(() => {
        doc_height = $(document).height();
    });

    // Recalculcate positions on ajax and img load events.
    // Inside the handlers the current document height is compared
    // to the previous height before the event occured so recalculation
    // only happens on actual changes
    $(document).on('ajaxComplete', heightChangeHandler);
    $(document).on('load', '#content img', heightChangeHandler);

    // Specialized handler to trigger recalculation when wysiwyg
    // instances are created.
    $(document).on('load.wysiwyg', 'textarea', function() {
        $(document.body).trigger('sticky_kit:recalc');
    });
}

// Engage
STUDIP.domReady(() => {
    STUDIP.Sidebar.checkActiveLineHeight();
});
