/**
 * This class contains Stud.IP specific code for the fullcalendar package.
 */

import { Calendar } from '@fullcalendar/core';
import deLocale from '@fullcalendar/core/locales/de';
import enLocale from '@fullcalendar/core/locales/en-gb';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import resourceCommonPlugin from '@fullcalendar/resource-common';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';

import { jsPDF } from 'jspdf';
import html2canvas from 'html2canvas';

Date.prototype.getWeekNumber = function () {
    var d = new Date(Date.UTC(this.getFullYear(), this.getMonth(), this.getDate()));
    var dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
    return Math.ceil((((d - yearStart) / 86400000) + 1)/7);
};

function pad(what, length = 2, char = '0') {
    let padding = new Array(length + 1).join(char);
    return `${padding}${what}`.substr(-length);
}

class Fullcalendar
{
    /**
     * The initialisation method. It loads the JS files for fullcalendar
     * in case they are not loaded and sets up a fullcalendar instance
     * for the nodes specified in the parameter node.
     *
     * @param DOMElement|string node The node which shall have a full calendar.
     *     This must either be a DOMElement or a string
     *     containing a CSS selector.
     */
    static init(node, fullcalendar_options = null)
    {
        // Convert css selector to actual dom element
        node = $(node)[0];

        if (!node) {
            //We need a CSS selector or a node!
            return;
        }

        if (document.getElementById('external-events')) {
            new Draggable(document.getElementById('external-events'), {
                itemSelector: '.fc-event',
                eventData (eventEl) {
                    return {
                        title: eventEl.dataset.eventTitle,
                        duration: eventEl.dataset.eventDuration,
                        course_id: eventEl.dataset.eventCourse,
                        tooltip: eventEl.dataset.eventTooltip,
                        studip_api_urls: {drop: eventEl.dataset.eventDropUrl},
                        studip_view_urls: {edit: eventEl.dataset.eventDetailsUrl}
                    };
                }
            });
        }

        var calendar = new Calendar(node, fullcalendar_options);
        node.calendar = calendar;
        calendar.render();

        return calendar;
    }

    /**
     * Converts semester events to the default fullcalendar event format.
     * The begin and end date are converted to fit into the current week.
     */
    static convertSemesterEvents(event_data, fake_week_start = Date())
    {
        if (!event_data) {
            return {};
        }

        var start = String(event_data.start).split('T');
        var end = String(event_data.end).split('T');

        //start and end must be transformed to the current week.
        //Therefore, we need the ISO weekdays for begin and end.
        var fake_start = new Date(fake_week_start);
        fake_start.setHours(12);
        fake_start.setMinutes(0);
        fake_start.setSeconds(0);
        var fake_end = new Date(fake_week_start);
        fake_end.setHours(12);
        fake_end.setMinutes(0);
        fake_end.setSeconds(0);

        //Calculcate the week day of the current week for the event
        //from the current day and convert sunday to ISO format
        var start_day_diff = fake_start.getDay() || 7;
        var end_day_diff = fake_end.getDay() || 7;

        start_day_diff = start_day_diff - event_data.studip_weekday_begin;
        end_day_diff = end_day_diff - event_data.studip_weekday_end;

        fake_start = new Date(
            fake_start.getTime() - start_day_diff * 24 * 60 * 60 * 1000
        );
        fake_end = new Date(
            fake_end.getTime() - end_day_diff * 24 * 60 * 60 * 1000
        );

        //Output the modified begin and end date in the correct ISO format:
        event_data.start =`${fake_start.getFullYear()}-${pad(fake_start.getMonth() + 1)}-${pad(fake_start.getDate())}T${start[1]}`;
        event_data.end = `${fake_end.getFullYear()}-${pad(fake_end.getMonth() + 1)}-${pad(fake_end.getDate())}T${end[1]}`;

        return event_data;
    }


    static createSemesterCalendarFromNode(node, additional_config = {})
    {
        if (!node) {
            //Ain't no fullcalendar when the node's gone!
            return;
        }

        var config = $.extend(
            {},
            $(node).data('config') || {},
            additional_config
        );

        if (Array.isArray(config.eventSources)) {
            config.eventSources = config.eventSources.map((s) => {
                if (s.url !== undefined) {
                    return s;
                }
            });
        }

        return this.createFromNode(node, config);
    }


    static defaultResizeEventHandler(info)
    {
        if (!info.event.durationEditable || !info.view.viewSpec.options.editable) {
            //Read-only events cannot be resized!
            info.revert();
            return;
        }

        if (info.event.extendedProps.studip_api_urls.resize) {
            $.post({
                url: info.event.extendedProps.studip_api_urls.resize,
                async: false,
                data: {
                    begin: this.toRFC3339String(info.event.start),
                    end: this.toRFC3339String(info.event.end)
                }
            }).fail(info.revert);
        } else if (info.event.extendedProps.studip_api_urls.resize_dialog) {
            STUDIP.Dialog.fromURL(
                info.event.extendedProps.studip_api_urls.resize_dialog,
                {
                    data: {
                        begin: this.toRFC3339String(info.event.start),
                        end: this.toRFC3339String(info.event.end)
                    }
                }
            );
        }
    }

    static downloadPDF(format = 'landscape', withWeekend = false)
    {
        $('*[data-fullcalendar="1"]').each(function () {
            if (this.calendar != undefined) {
                $(this).addClass('print-view').toggleClass('without-weekend', !withWeekend);

                var title = $(this).data('title');
                let print_title = $('<h1>').text(title).prependTo(this);

                window.scrollTo(0, 0);

                html2canvas(this).then(canvas => {
                    var imgData = canvas.toDataURL('image/jpeg');
                    var pdf = new jsPDF({
                        orientation: format === 'landscape' ? 'landscape' : 'portrait'
                    });
                    if (format === 'landscape') {
                        pdf.addImage(imgData, 'JPEG', 20, 20, 250, 250, 'i1', 'NONE', 0);
                    } else {
                        pdf.addImage(imgData, 'JPEG', 25, 20, 160, 190, 'i1', 'NONE', 0);
                    }
                    pdf.save(title + '.pdf');
                });

                print_title.remove();
                $(this).removeClass('print-view without-weekend');
            }
        });
    }

    static toRFC3339String(date)
    {
        var timezone_offset_min = date.getTimezoneOffset();
        var offset_hrs = parseInt(Math.abs(timezone_offset_min / 60), 10);
        var offset_min = Math.abs(timezone_offset_min%60);
        var timezone_standard;

        offset_hrs = pad(offset_hrs);
        offset_min = pad(offset_min);

        // Add an opposite sign to the offset
        // If offset is 0, it means timezone is UTC
        if (timezone_offset_min < 0) {
            timezone_standard = `+${offset_hrs}:${offset_min}`;
        } else if (timezone_offset_min > 0) {
            timezone_standard = `-${offset_hrs}:${offset_min}`;
        } else {
            timezone_standard = '+00:00';
        }

        var current_date  = pad(date.getDate());
        var current_month = pad(date.getMonth() + 1);
        var current_year  = date.getFullYear();
        var current_hrs   = pad(date.getHours());
        var current_mins  = pad(date.getMinutes());
        var current_secs  = pad(date.getSeconds());
        var current_datetime;

        // Current datetime
        // String such as 2016-07-16T19:20:30
        current_datetime = `${current_year}-${current_month}-${current_date}T${current_hrs}:${current_mins}:${current_secs}`;

        return current_datetime + timezone_standard;
    }

    static defaultDropEventHandler(info)
    {
        // The logic from fullcalendar is inversed here:
        // If the calendar isn't editable, the event isn't either.
        if (!info.event.startEditable || !info.view.viewSpec.options.editable) {
            //Read-only events cannot be dragged and dropped!
            info.revert();
            return;
        }

        var drop_resource_id = info.newResource ? info.newResource.id : info.event.extendedProps.studip_range_id;

        if (info.event.extendedProps.studip_api_urls.move || info.event.extendedProps.studip_api_urls.move_dialog) {
            let move_dialog = info.event.extendedProps.studip_api_urls.move_dialog;
            if (info.event.allDay) {
                if (move_dialog) {
                    STUDIP.Dialog.fromURL(
                        move_dialog,
                        {
                            data: {
                                resource_id: drop_resource_id,
                                begin: this.toRFC3339String(info.event.start.setHours(0, 0, 0)),
                                end: this.toRFC3339String(info.event.start.setHours(23, 59, 59))
                            }
                        }
                    );
                } else {
                    jQuery.post({
                        async: false,
                        url: info.event.extendedProps.studip_api_urls.move,
                        data: {
                            resource_id: drop_resource_id,
                            begin: this.toRFC3339String(info.event.start.setHours(0, 0, 0)),
                            end: this.toRFC3339String(info.event.start.setHours(23, 59, 59))
                        }
                    }).fail(info.revert);
                }
            } else if (info.event.end === null) {
                let real_end = new Date();
                real_end.setTime(info.event.start.getTime());
                real_end.setHours(info.event.start.getHours()+2);
                if (move_dialog) {
                    STUDIP.Dialog.fromURL(
                        move_dialog,
                        {
                            data: {
                                resource_id: drop_resource_id,
                                begin: this.toRFC3339String(info.event.start),
                                end: this.toRFC3339String(real_end)
                            }
                        }
                    );
                } else {
                    jQuery.post({
                        async: false,
                        url: info.event.extendedProps.studip_api_urls.move,
                        data: {
                            resource_id: drop_resource_id,
                            begin: this.toRFC3339String(info.event.start),
                            end: this.toRFC3339String(real_end)
                        }
                    }).fail(info.revert);
                }
            } else {
                if (move_dialog) {
                    STUDIP.Dialog.fromURL(
                        move_dialog,
                        {
                            data: {
                                resource_id: drop_resource_id,
                                begin: this.toRFC3339String(info.event.start),
                                end: this.toRFC3339String(info.event.end)
                            }
                        }
                    );
                } else {
                    jQuery.post({
                        async: false,
                        url: info.event.extendedProps.studip_api_urls.move,
                        data: {
                            resource_id: drop_resource_id,
                            begin: this.toRFC3339String(info.event.start),
                            end: this.toRFC3339String(info.event.end)
                        }
                    }).fail(info.revert);
                }
            }
        }
    }

    static institutePlanDropEventHandler(info)
    {
        //The logic from fullcalendar is inversed here:
        if (info.newResource) {
            $.post({
                async: false,
                url: info.event.extendedProps.studip_api_urls.move,
                data: {
                    cycle_id: info.event.id,
                    resource_id: info.newResource.id,
                    begin: this.toRFC3339String(info.event.start),
                    end: this.toRFC3339String(info.event.end)
                }
            }).fail(info.revert);
        } else {
            //If the calendar isn't editable, the event isn't either.
            if (!info.event.startEditable || !info.view.viewSpec.options.editable) {
                //Read-only events cannot be dragged and dropped!
                info.revert();
                return;
            }

            $.post({
                async: false,
                url: info.event.extendedProps.studip_api_urls.move,
                data: {
                    cycle_id: info.event.id,
                    begin: this.toRFC3339String(info.event.start),
                    end: this.toRFC3339String(info.event.end)
                }
            }).fail(info.revert);
        }
    }

    static institutePlanExternalDropEventHandler(info)
    {
        var resourceIds = info.event.getResources().map(resource => resource.id);

        $.post({
            async: false,
            url: info.event.extendedProps.studip_api_urls.drop,
            data: {
                course_id: info.event.extendedProps.course_id,
                begin: this.toRFC3339String(info.event.start),
                end: this.toRFC3339String(info.event.end),
                resource_id: resourceIds[0]
            }
        }).done(data => {
            if (data) {
                info.view.context.calendar.addEvent(JSON.parse(data));
                info.event.remove();
            }
        });
    }

    static createFromNode(node, additional_config = {})
    {
        if (!node) {
            //No node? No fullcalendar!
            return;
        }

        var config = $(node).data('config');

        //Make sure the default values are set, if they are not found
        //in the additional_config object:
        config = $.extend({
            plugins: [ interactionPlugin, dayGridPlugin, timeGridPlugin, resourceCommonPlugin, resourceTimeGridPlugin, resourceTimelinePlugin ],
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            defaultView: 'timeGridWeek',
            header: {
                left: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            minTime: '08:00:00',
            maxTime: '20:00:00',
            validRange: {
                start: '1970-01-01'
            },
            height: 'auto',
            contentHeight: 'auto',
            firstDay: 1,
            weekNumberCalculation: 'ISO',
            locales: [enLocale, deLocale ],
            locale:  String.locale === 'de-DE' ? 'de' : 'en-gb',
            timeFormat: 'H:mm',
            nowIndicator: true,
            timeZone: 'local',
            studip_functions: [],
            resourceAreaWidth: '20%',
            select (selectionInfo) {
                let calendar_config = JSON.parse(selectionInfo.view.context.calendar.el.dataset.config);
                let dialog_size = 'auto';
                if (calendar_config.dialog_size !== undefined) {
                    dialog_size = calendar_config.dialog_size;
                }

                if (!selectionInfo.view.viewSpec.options.editable || !selectionInfo.view.viewSpec.options.studip_urls) {
                    //The calendar isn't editable.
                    return;
                }
                if (selectionInfo.view.viewSpec.options.studip_urls.add) {
                    if (selectionInfo.resource) {
                        STUDIP.Dialog.fromURL( selectionInfo.view.viewSpec.options.studip_urls.add, {
                            data: {
                                begin: selectionInfo.start.getTime()/1000,
                                end: selectionInfo.end.getTime()/1000,
                                ressource_id: selectionInfo.resource.id,
                                all_day: selectionInfo.allDay ? '1' : '0'
                            },
                            size: dialog_size
                        });
                    } else {
                        STUDIP.Dialog.fromURL(selectionInfo.view.viewSpec.options.studip_urls.add, {
                            data: {
                                begin: selectionInfo.start.getTime()/1000,
                                end: selectionInfo.end.getTime()/1000,
                                all_day: selectionInfo.allDay ? '1' : '0'
                            },
                            size: dialog_size
                        });
                    }
                }
            },
            eventClick (eventClickInfo) {
                var event = eventClickInfo.event;
                var extended_props = event.extendedProps;
                if ($(eventClickInfo.jsEvent.target).hasClass('event-colorpicker')) {
                    STUDIP.Dialog.fromURL(
                        STUDIP.URLHelper.getURL('dispatch.php/admin/courseplanning/pick_color/' + extended_props.metadate_id + '/' + config.actionCalled),
                        {'size': '400x400'}
                    );
                    return false;
                }


                if ($(eventClickInfo.event._calendar.el).hasClass('request-plan')) {
                    if (extended_props.request_id && extended_props.studip_view_urls.edit) {
                        STUDIP.Dialog.fromURL(
                            STUDIP.URLHelper.getURL(extended_props.studip_view_urls.edit)
                        );
                    } else if(extended_props.studip_parent_object_class == 'ResourceBooking' && $.inArray('for-course', event._def.ui.classNames) != -1) {
                        STUDIP.Dialog.fromURL(
                            STUDIP.URLHelper.getURL('dispatch.php/resources/room_request/rerequest_booking/' + extended_props.studip_parent_object_id)
                        );
                    }
                    return false;
                }

                if (extended_props.studip_view_urls === undefined) {
                    return;
                }
                let calendar_config = JSON.parse(eventClickInfo.view.context.calendar.el.dataset.config);
                let dialog_size = 'auto';
                if (calendar_config.dialog_size !== undefined) {
                    //Use the configured default dialog size for the fullcalendar instance:
                    dialog_size = calendar_config.dialog_size;
                }
                if (extended_props.dialog_size !== undefined) {
                    //Use the dialog size of the event:
                    dialog_size = extended_props.dialog_size;
                }
                if (!event.startEditable && extended_props.studip_view_urls.show) {
                    STUDIP.Dialog.fromURL(
                        STUDIP.URLHelper.getURL(extended_props.studip_view_urls.show),
                        {size: dialog_size}
                    );
                } else if (event.startEditable) {
                    if (extended_props.studip_view_urls.edit) {
                        STUDIP.Dialog.fromURL(
                            STUDIP.URLHelper.getURL(extended_props.studip_view_urls.edit),
                            {size: dialog_size}
                        );
                    } else if (extended_props.studip_view_urls.show) {
                        STUDIP.Dialog.fromURL(
                            STUDIP.URLHelper.getURL(extended_props.studip_view_urls.show),
                            {size: dialog_size}
                        );
                    }
                }
                return false;
            },
            eventResize (info) {
                // The logic from fullcalendar is inversed here:
                // If the calendar isn't editable, the event isn't either.
                if (info.view.viewSpec.options.studip_functions.resize_event) {
                    info.view.viewSpec.options.studip_functions.resize_event(info);
                } else {
                    STUDIP.Fullcalendar.defaultResizeEventHandler(info);
                }
                info.event.source.refetch();
            },
            eventDrop (info) {
                let handle_drop = function() {
                    if ($(info.view.context.calendar.el).hasClass('institute-plan')) {
                        var start = info.event.start;
                        var cal_start = info.view.activeStart;
                        if ((start.getHours() - cal_start.getHours()) % 2 === 1) {
                            info.event.moveDates('-01:00');
                        }
                        STUDIP.Fullcalendar.institutePlanDropEventHandler(info);
                    } else {
                        if (info.view.viewSpec.options.studip_functions.drop_event) {
                            info.view.viewSpec.options.studip_functions.drop_event(info);
                        } else {
                            STUDIP.Fullcalendar.defaultDropEventHandler(info);
                        }
                        info.event.source.refetch();
                    }
                };

                let calendar_config = JSON.parse(info.view.context.calendar.el.dataset.config);
                if (calendar_config.confirm) {
                    if (calendar_config.confirm.drop) {
                        STUDIP.Dialog.confirm(calendar_config.confirm.drop)
                            .done(handle_drop)
                            .fail(function() {
                                //Revert the dropped element:
                                info.revert();
                            });
                    } else {
                        handle_drop();
                    }
                } else {
                    handle_drop();
                }
            },
            eventRender (info) {
                var event = info.event;
                var eventElement = info.el;
                var iconColor = event.textColor == '#000000' ? 'black' : 'white';

                if ($(info.view.context.calendar.el).hasClass('institute-plan')) {
                    $(eventElement).attr('title', event.extendedProps.tooltip);
                    $(eventElement).find('.fc-title').html(
                        $('<div>').css({
                            width: 'calc(100% - 21px)',
                            height: '100%',
                            wordBreak: 'break-word'
                        }).text(eventElement.text)
                    );
                    $(eventElement).find('.fc-title').append(
                        $('<button class="event-colorpicker">').addClass(iconColor)
                    );
                } else {
                    $(eventElement).attr('title', event.title);
                }

                if (event.extendedProps.icon) {
                    $(eventElement).find('.fc-title').prepend(
                        $('<img>').attr('src', `${STUDIP.ASSETS_URL}images/icons/${iconColor}/${event.extendedProps.icon}.svg`)
                            .css({
                                verticalAlign: 'text-bottom',
                                marginRight: '3px',
                                width: 14,
                                height: 14
                            })
                    );
                }
            },
            eventSourceSuccess: function(content, xhr) {
                if ($(node).hasClass('semester-plan')) {
                    $(content).each(function(i, event_data){
                        STUDIP.Fullcalendar.convertSemesterEvents(event_data, config.defaultDate);
                    });
                }
                return content;
            },
            loading (isLoading) {
                if (isLoading) {
                    if (!$('#loading-spinner').length) {
                        jQuery('#content').append(
                            $('<div id="loading-spinner" style="position: absolute; top: calc(50% - 55px); left: calc(50% + 135px); z-index: 9001;">').html(
                                $('<img>').attr('src', STUDIP.ASSETS_URL + 'images/loading-indicator.svg')
                                    .css({
                                        width: 64,
                                        height: 64
                                    })
                            )
                        );
                    }
                } else {
                    $('#loading-spinner').remove();
                    this.updateSize();
                }
            },
            datesRender (info) {
                let activeRange = info.view.props.dateProfile.activeRange;
                let timestamp = activeRange.start.getTime() / 1000;
                if ($(info.el).hasClass('institute-plan')) {
                    $('.fc-slats tr:odd .fc-widget-content:not(.fc-axis)').remove();
                }

                STUDIP.api.GET(`semester/${timestamp}/week`).done((data) => {

                    if (data) {
                        $('#booking-plan-header-semname').text(data.semester_name);
                        if (data.sem_week) {
                            $('#booking-plan-header-semweek').text(data.sem_week);
                            $('#booking-plan-header-semweek-part').show();
                        } else {
                            $('#booking-plan-header-semweek').text('');
                            $('#booking-plan-header-semweek-part').hide();
                        }
                        $('#booking-plan-header-semrow').show();
                        $('#booking-plan-header-calweek').text(data.week_number);
                        $('#booking-plan-header-calbegin').text('(' + data.current_day + ')');
                    } else {
                        $('#booking-plan-header-semrow').hide();
                        $('#booking-plan-header-semweek-part').hide();
                    }
            })
            },
            resourceRender (renderInfo) {
                if ($(renderInfo.view.context.calendar.el).hasClass('room-group-booking-plan')) {
                    let action = $(renderInfo.view.context.calendar.el).hasClass('semester-plan') ? 'semester' : 'booking';
                    let url = STUDIP.URLHelper.getURL(`dispatch.php/resources/room_planning/${action}_plan/${renderInfo.resource.id}`);
                    $(renderInfo.el).find('.fc-cell-text').html(
                        $('<a>').attr('href', url).text(renderInfo.resource.title)
                    );
                } else if ($("*[data-fullcalendar='1']").hasClass('institute-plan') && renderInfo.resource.id > 0) {
                    let icon = '<img class="text-bottom icon-role-clickable icon-shape-edit" width="16" height="16" src="' + STUDIP.URLHelper.getURL('assets/images/icons/blue/edit.svg') + '" alt="edit">';
                    $(renderInfo.el).append(
                        '<a href="'
                        + STUDIP.URLHelper.getURL('dispatch.php/admin/courseplanning/rename_column/'
                        + renderInfo.resource.id
                        +'/'
                        + renderInfo.view.activeStart.getDay())
                        + '" data-dialog="size=auto"> '
                        + icon
                        + '</a>'
                    );
                }
            },
            drop (dropInfo) {
                $(dropInfo.draggedEl).remove();
            },
            eventReceive (info) {
                if ($(info.view.context.calendar.el).hasClass('institute-plan')) {
                    STUDIP.Fullcalendar.institutePlanExternalDropEventHandler(info);
                }
            }
        }, config);

        //Special treatment: If a general column header format is set,
        //in the configuration, it shall be used for all columns in all views
        //by using a special columnHeaderHtml function.
        if (config.columnHeaderFormat) {
            config.columnHeaderHtml = function (date) {
                if ($("*[data-fullcalendar='1']").hasClass('institute-plan')) {
                    return '<a href="' + STUDIP.URLHelper.getURL('dispatch.php/admin/courseplanning/weekday/' + date.getDay()) + '">' + date.toLocaleDateString('de-DE', config.columnHeaderFormat) + '</a>';
                } else {
                    return date.toLocaleDateString('de-DE', config.columnHeaderFormat);
                }
            };
        }

        config = $.extend({}, config, additional_config);

        return this.init(node, config);
    }

    static submitDatePicker() {
        let picked_date = jQuery('#booking-plan-jmpdate').val();
        let booking_plan_datepicker = true;
        if (!picked_date) {
            //Not a booking plan date selector.
            picked_date = jQuery('#date_select').val();
            booking_plan_datepicker = false;
        }
        let iso_date_string = '';
        if (picked_date) {
            if (picked_date.includes('.')) {
                let [day, month, year] = picked_date.split('.');
                iso_date_string = year.padStart(4, '20') + '-' + month.padStart(2, '0') + '-' + day.padStart(2, '0');
            } else if (picked_date.includes('/')) {
                let [day, month, year] = picked_date.split('/');
                iso_date_string = year.padStart(4, '20') + '-' + month.padStart(2, '0') + '-' + day.padStart(2, '0');
            } else if (picked_date.includes('-')) {
                iso_date_string = picked_date;
            }
        }
        if (iso_date_string) {
            jQuery('[data-fullcalendar="1"],[data-resources-fullcalendar="1"]').each(function () {
                this.calendar.gotoDate(iso_date_string);
            });
            if (booking_plan_datepicker) {
                Fullcalendar.updateDateURL();
            }
        }
    }

    static updateDateURL() {
        let changedMoment;
        jQuery('[data-fullcalendar="1"],[data-resources-fullcalendar="1"]').each(function () {
            changedMoment = this.calendar.getDate();
        });
        if (changedMoment) {
            let changed_date = STUDIP.Fullcalendar.toRFC3339String(changedMoment).split('T')[0];
            //Get the timestamp:
            let timestamp = changedMoment.getTime() / 1000;

            jQuery('a.resource-bookings-actions').each(function () {
                const url = new URL(this.href);
                url.searchParams.set('timestamp', timestamp)
                url.searchParams.set('defaultDate', changed_date)
                this.href = url.toString();
            });

            // Now change the URL of the window.
            const url = new URL(window.location.href);
            url.searchParams.set('defaultDate', changed_date);

            // Update url in history
            history.pushState({}, null, url.toString());

            // Adjust links accordingly
            url.searchParams.delete('allday');
            jQuery('.booking-plan-std_view').attr('href', url.toString());

            url.searchParams.set('allday', 1);
            jQuery('.booking-plan-allday_view').attr('href', url.toString());

            // Update sidebar value
            let element = jQuery('#booking-plan-jmpdate,#date_select').first();
            element.val(changedMoment.toLocaleDateString('de-DE'));
            if (element.is('#booking-plan-jmpdate')) {
                //Store the date in the sessionStorage:
                sessionStorage.setItem('booking_plan_date', changed_date);
            }
        }
    }
}

export default Fullcalendar;
