/* ------------------------------------------------------------------------
 * Anmeldeverfahren und -sets
 * ------------------------------------------------------------------------ */

STUDIP.domReady(function () {
    $(document).on('change', 'tr.course input', function(i) {
        STUDIP.Admission.toggleNotSavedAlert();
    });

    $('a.userlist-delete-user').on('click', function(event) {
        $(this).closest('tr').remove();
        return false;
    });

    $('#courseset-form .autosave').on('click', (event) => {
        STUDIP.Admission.autosaveCourseset();
    });

    STUDIP.ready(() => {
        $('#toggle-date-link').on('click', (event) => {
            $('#admissionrule-valid-date').toggleClass('hidden-js');
        });
    });
});
