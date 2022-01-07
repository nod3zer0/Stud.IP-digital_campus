/* ------------------------------------------------------------------------
 * SemClass administration - only for root-user
 * ------------------------------------------------------------------------ */

const admin_sem_class = {
    make_sortable: function() {
        jQuery('#plugins .droparea').sortable({
            revert: true,
        });

    },
    saveData: function() {

        var modules = {};
        jQuery('div.plugin').each(function() {
            var activated = jQuery(this)
                .find('input[name=active]')
                .is(':checked');
            var sticky =
                !jQuery(this)
                    .find('input[name=nonsticky]')
                    .is(':checked');
            var module_name = jQuery(this).attr('id');
            if (module_name) {
                module_name = module_name.substr(module_name.indexOf('_') + 1);
            }
            modules[module_name] = {
                activated: +activated,
                sticky: +sticky
            };
        });
        jQuery('#message_below').html('');
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/save',
            data: {
                sem_class_id: jQuery('#sem_class_id').val(),
                sem_class_name: jQuery('#sem_class_name').val(),
                sem_class_description: jQuery('#sem_class_description').val(),
                title_dozent: !jQuery('#title_dozent_isnull').is(':checked') ? jQuery('#title_dozent').val() : '',
                title_dozent_plural: !jQuery('#title_dozent_isnull').is(':checked')
                    ? jQuery('#title_dozent_plural').val()
                    : '',
                title_tutor: !jQuery('#title_tutor_isnull').is(':checked') ? jQuery('#title_tutor').val() : '',
                title_tutor_plural: !jQuery('#title_tutor_isnull').is(':checked')
                    ? jQuery('#title_tutor_plural').val()
                    : '',
                title_autor: !jQuery('#title_autor_isnull').is(':checked') ? jQuery('#title_autor').val() : '',
                title_autor_plural: !jQuery('#title_autor_isnull').is(':checked')
                    ? jQuery('#title_autor_plural').val()
                    : '',
                modules: modules,
                workgroup_mode: jQuery('#workgroup_mode').is(':checked') ? 1 : 0,
                studygroup_mode: jQuery('#studygroup_mode').is(':checked') ? 1 : 0,
                only_inst_user: jQuery('#only_inst_user').is(':checked') ? 1 : 0,
                default_read_level: jQuery('#default_read_level').val(),
                default_write_level: jQuery('#default_write_level').val(),
                bereiche: jQuery('#bereiche').is(':checked') ? 1 : 0,
                module: jQuery('#module').is(':checked') ? 1 : 0,
                show_browse: jQuery('#show_browse').is(':checked') ? 1 : 0,
                write_access_nobody: jQuery('#write_access_nobody').is(':checked') ? 1 : 0,
                topic_create_autor: jQuery('#topic_create_autor').is(':checked') ? 1 : 0,
                visible: jQuery('#visible').is(':checked') ? 1 : 0,
                course_creation_forbidden: jQuery('#course_creation_forbidden').is(':checked') ? 1 : 0,
                create_description: jQuery('#create_description').val(),
                admission_prelim_default: jQuery('#admission_prelim_default').val(),
                admission_type_default: jQuery('#admission_type_default').val(),
                show_raumzeit: jQuery('#show_raumzeit').is(':checked') ? 1 : 0,
                is_group: jQuery('#is_group').is(':checked') ? 1 : 0
            },
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                jQuery('#message_below').html(data.html);
            }
        });
    },
    delete_sem_type_question: function() {
        var sem_type = jQuery(this)
            .closest('li')
            .attr('id');
        sem_type = sem_type.substr(sem_type.lastIndexOf('_') + 1);
        jQuery('#sem_type_for_deletion').val(sem_type);
        jQuery('#sem_type_delete_question').dialog({
            title: jQuery('#sem_type_delete_question_title').text()
        });
    },
    add_sem_type: function() {
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/add_sem_type',
            type: 'post',
            data: {
                sem_class: jQuery('#sem_class_id').val(),
                name: jQuery('#new_sem_type').val()
            },
            success: function(ret) {
                jQuery('#sem_type_list').append(jQuery(ret));
                jQuery('#new_sem_type')
                    .val('')
                    .closest('li')
                    .children()
                    .toggle();
            },
            error: function() {
                jQuery('#new_sem_type')
                    .val('')
                    .closest('li')
                    .children()
                    .toggle();
            }
        });
    },
    delete_sem_type: function() {
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/delete_sem_type',
            data: {
                sem_type: jQuery('#sem_type_for_deletion').val()
            },
            type: 'post',
            success: function() {
                jQuery('#sem_type_' + jQuery('#sem_type_for_deletion').val()).remove();
                jQuery('#sem_type_delete_question').dialog('close');
            }
        });
    },
    rename_sem_type: function() {
        jQuery(this)
            .closest('span.name_container')
            .children()
            .toggle();
        var name = this.value;
        var old_name = jQuery(this)
            .closest('.name_container')
            .find('.name_html');
        var sem_type = jQuery(this)
            .closest('li')
            .attr('id');
        sem_type = sem_type.substr(sem_type.lastIndexOf('_') + 1);
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/admin/sem_classes/rename_sem_type',
            data: {
                sem_type: sem_type,
                name: name
            },
            type: 'post',
            success: function() {
                old_name.text(name);
            }
        });
    }
};

export default admin_sem_class;
