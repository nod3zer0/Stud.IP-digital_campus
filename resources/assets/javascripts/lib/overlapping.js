import { $gettext } from './gettext';

const Overlapping = {

    /**
     * Initialize Select2 select boxes.
     * @returns {undefined}
     */
    init: function () {
        $('#base-version-select').select2({
            placeholder: $gettext('Studiengangteil suchen'),
            minimumInputLength: 3,
            ajax: {
                url: STUDIP.URLHelper.getURL('dispatch.php/admin/overlapping/base_version'),
                dataType: 'json'
            }
        });

        $('#comp-versions-select').select2({
            placeholder: $gettext('Optional weitere Studiengangteile (max. 5)'),
            minimumInputLength: 3,
            ajax: {
                url: STUDIP.URLHelper.getURL('dispatch.php/admin/overlapping/comp_versions'),
                dataType: 'json'
            }
        });

        $('#fachsem-select').select2({
            placeholder: $gettext('Fachsemester auswählen (optional)')
        });
        $('#semtype-select').select2({
            placeholder: $gettext('Veranstaltungstyp auswählen (optional)')
        });
        $('#base-version-select').on('select2:select', function (e) {
            $('#comp-versions-select').val(null).trigger('change');
            $.ajax({
                url: STUDIP.URLHelper.getURL('dispatch.php/admin/overlapping/comp_versions'),
                dataType: 'json',
                data: {
                    version_id: $('#base-version-select').select2('data')[0].id
                },
                success: function(data) {
                    if (data.results.length) {
                        var inputlength = 3;
                        if (data.results.length < 4) {
                            inputlength = 0;
                        }
                        $('#comp-versions-select').select2({
                            placeholder: $gettext('Optional weitere Studiengangteile (max. 5)'),
                            minimumInputLength: inputlength,
                            ajax: {
                                url: STUDIP.URLHelper.getURL('dispatch.php/admin/overlapping/comp_versions',
                                    {'version_id': $('#base-version-select').select2('data')[0].id}),
                                dataType: 'json'
                            }
                        });
                    } else {
                        $('#comp-versions-select').select2({
                            placeholder: $gettext('Keine weitere Auswahl möglich')
                        });
                        $('#comp-versions-select').prop('disabled', true).trigger('change');
                    }
                }
            });
        });

        $('span.mvv-overlapping-exclude').on('click', function () {
            var course_id = $(this).data('mvv-ovl-course');
            var selection_id = $(this).data('mvv-ovl-selection');
            $.ajax({
                method: 'post',
                url: STUDIP.URLHelper.getURL('dispatch.php/admin/overlapping/set_exclude'),
                data: {
                    'excluded': $(this).is('.mvv-overlapping-invisible') ? 1 : 0,
                    'course_id': course_id,
                    'selection_id': selection_id
                },
                success: function(data, textStatus, jqXHR) {
                    $('.mvv-overlapping-exclude').each(function () {
                        if ($(this).data('mvv-ovl-course') == course_id) {
                            $(this).toggleClass('mvv-overlapping-invisible');
                        }
                    });
                    $('.mvv-overlapping-exclude').attr('title', $gettext('Veranstaltung berücksichtigen'));
                    $('.mvv-overlapping-invisible').attr('title', $gettext('Veranstaltung nicht berücksichtigen'));

                }
            })
            return false;
        });
    }
};

export default Overlapping;
