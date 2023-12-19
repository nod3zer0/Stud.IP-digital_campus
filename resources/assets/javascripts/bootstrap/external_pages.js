import {$gettext} from "../lib/gettext";

STUDIP.domReady(function () {
    const ExternalPage = document.querySelector('#institute-extern-edit, #admin-extern-edit');
    if (ExternalPage !== null) {
        jQuery('#select-study-areas').select2({
            placeholder:  $gettext('Studienbereich suchen'),
            minimumInputLength: 3,
            ajax: {
                url: STUDIP.URLHelper.getURL('dispatch.php/admin/extern/search_studyareas'),
                dataType: 'json'
            }
        });
    }
});
