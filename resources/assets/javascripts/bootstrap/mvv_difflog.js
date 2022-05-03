import { $gettextInterpolate } from  '../lib/gettext.js';

STUDIP.domReady(() => {
    $('del.diffdel').each(function() {
        var mvv_field = '';

        $(this)
            .parentsUntil('div')
            .each(function() {
                if ($(this).attr('data-mvv-field')) {
                    mvv_field = $(this).attr('data-mvv-field');
                    return true;
                }
            });

        if (mvv_field != '') {
            var mvv_id;
            var senddata;
            $(this)
                .parentsUntil('div')
                .each(function() {
                    if ($(this).attr('data-mvv-id')) {
                        mvv_id = $(this).attr('data-mvv-id');
                        return true;
                    }
                });
            var mvv_debug = $(this).text();

            var del = $(this);
            var fields = mvv_field.split(' ');

            for (var i = 0; i < fields.length; ++i) {
                var obj_elements = fields[i].split('.');

                if (obj_elements.length == 1) {
                    senddata = { mvv_field: fields[i], mvv_debug: mvv_debug, log_action: 'del' };
                } else {
                    senddata = { mvv_field: fields[i], mvv_id: mvv_id, log_action: 'update' };
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    senddata,
                    function(data) {
                        if (data) {
                            var info = $gettextInterpolate('Entfernt von %{user} am %{time}', data);
                            del.attr('title', info);
                            $('<del class="difflog"/>').text(` [${info}] `).insertAfter(del);
                        }
                    },
                    'json'
                );
            }
        }
    });

    $('ins').each(function() {
        var mvv_field = '';
        var mvv_coid = '';
        var mvv_id = '';
        var mvv_log_action;

        switch ($('ins').attr('class')) {
            case 'diffins':
                mvv_log_action = 'new';
                break;
            case 'diffmod':
                mvv_log_action = 'update';
                break;
            default:
                mvv_log_action = null;
                break;
        }

        $(this)
            .parentsUntil('div')
            .each(function() {
                if ($(this).attr('data-mvv-field')) {
                    mvv_field = $(this).attr('data-mvv-field');
                    mvv_coid = $(this).attr('data-mvv-coid');
                    return false;
                }
            });

        if (mvv_field != '') {
            $(this)
                .parentsUntil('div')
                .each(function() {
                    if ($(this).attr('data-mvv-id')) {
                        mvv_id = $(this).attr('data-mvv-id');
                        return false;
                    }
                });

            var ins = $(this);
            var fields = mvv_field.split(' ');
            for (var i = 0; i < fields.length; ++i) {
                var senddata;
                var obj_elements = fields[i].split('.');
                if (obj_elements.length == 1 && mvv_coid) {
                    senddata = {
                        mvv_field: fields[i],
                        mvv_id: mvv_id,
                        mvv_coid: mvv_coid,
                        log_action: mvv_log_action
                    };
                } else if (fields[i] == 'mvv_modulteil_stgteilabschnitt.differenzierung' && mvv_coid) {
                    var classes = $(this)
                        .parent()
                        .attr('class')
                        .split(' ');
                    if (classes.length > 1) {
                        var mvv_debug =
                            $(this)
                                .parent()
                                .attr('data-mvv-index') +
                            ';' +
                            classes[1];
                        senddata = {
                            mvv_field: fields[i],
                            mvv_id: mvv_id,
                            mvv_coid: mvv_coid,
                            log_action: mvv_log_action,
                            mvv_debug: mvv_debug
                        };
                    } else {
                        return true;
                    }
                } else {
                    senddata = { mvv_field: fields[i], mvv_id: mvv_id, log_action: mvv_log_action };
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    senddata,
                    function(data) {
                        if (data) {
                            var info = $gettextInterpolate('Änderung durch %{user} am %{time}', data);
                            ins.attr('title', info);
                            $('<ins class="difflog"/>').text(` [${info}] `).insertAfter(ins);
                        }
                    },
                    'json'
                );
            }
        }
    });

    $('.mvv-diff-added').each(function() {
        $(this)
            .find('table')
            .each(function() {
                if ($(this).attr('data-mvv-type')) {
                    var mvv_type = $(this).attr('data-mvv-type');
                    var mvv_id = $(this).attr('data-mvv-id');
                    var curtable = $(this);
                } else {
                    return true;
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    { mvv_field: 'mvv_' + mvv_type, mvv_id: mvv_id, log_action: 'new' },
                    onSuccess,
                    'json'
                );
                function onSuccess(data) {
                    if (data) {
                        var info = $gettextInterpolate('Hinzugefügt von %{user} am %{time}', data);
                        curtable.attr('title', info);
                        const log = $('<ins class="difflog"/>').text(` [${info}] `);
                        const cell = $('<td/>').append(log);
                        const row = $('<tr/>').append(cell);
                        curtable.append(row);
                    }
                };
            });
    });

    $('.mvv-diff-deleted').each(function() {
        $(this)
            .find('table')
            .each(function() {
                if ($(this).attr('data-mvv-type')) {
                    var mvv_type = $(this).attr('data-mvv-type');
                    var mvv_id = $(this).attr('data-mvv-id');
                    var curtable = $(this);
                } else {
                    return true;
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    { mvv_field: 'mvv_' + mvv_type, mvv_id: mvv_id, log_action: 'del' },
                    onSuccess,
                    'json'
                );
                function onSuccess(data) {
                    if (data) {
                        var info = $gettextInterpolate('Entfernt von %{user} am %{time}', data);
                        curtable.attr('title', info);
                        const log = $('<del class="difflog"/>').text(` [${info}] `);
                        const cell = $('<td/>').append(log);
                        const row = $('<tr/>').append(cell);
                        curtable.append(row);
                    }
                }
            });
    });
});
