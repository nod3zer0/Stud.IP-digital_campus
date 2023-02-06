// Build responsive menu on domready or resize
STUDIP.domReady(() => {
    STUDIP.Responsive.engage();

    if (STUDIP.Responsive.isCompactNavigation()) {
        document.documentElement.classList.add('fullscreen-mode');
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.style.display = 'none';
        }
    }

}, true);

// Trigger search in responsive display
$(document).on('click', '#quicksearch .quicksearchbutton', function() {
    if ($('html').is(':not(.responsive-display)') || $('#quicksearch').is('.open')) {
        return;
    }

    $('#quicksearch').addClass('open');
    $('.quicksearchbox').focus();

    return false;
}).on('blur', '#quicksearch.open .quicksearchbox', function() {
    if (!this.value.trim().length) {
        $('#quicksearch').removeClass('open');
    }
}).on('autocompleteopen', event => {
    if ($(event.target).closest('#quicksearch').length === 0) {
        return;
    }
    $('body > .ui-autocomplete').css({
        left: 0,
        right: 0,
        boxSizing: 'border-box'
    });
});
