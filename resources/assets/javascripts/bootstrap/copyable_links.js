import { $gettext } from '../lib/gettext.js';

$(document).on('click', 'a.copyable-link', function (event) {
    event.preventDefault();

    // Create dummy element and position it off screen
    // This element must be "visible" (as in "not hidden") or otherwise
    // the copy command will fail
    let dummy = $('<textarea>').val(this.href).css({
        position: 'absolute',
        left: '-9999px'
    }).appendTo('body');

    // Select text and copy it to clipboard
    dummy[0].select();
    document.execCommand('Copy');
    dummy.remove();

    // Show visual hint using a deferred (this way we don't need to
    // duplicate the functionality in the done() handler)
    (new Promise((resolve, reject) => {
        let confirmation = $('<div class="copyable-link-confirmation copyable-link-success">');
        confirmation.text($gettext('Link wurde kopiert'));
        confirmation.insertBefore('#content');

        // Resolve deferred when animation has ended or after 2 seconds as a
        // fail safe
        let timeout = setTimeout(() => {
            $(this).parent().off('animationend');
            resolve(confirmation);
        }, 1500);
        $(this).parent().one('animationend', () => {
            clearTimeout(timeout);
            resolve(confirmation);
        });
    })).then((confirmation, parent) => {
        confirmation.remove();
    });
});
