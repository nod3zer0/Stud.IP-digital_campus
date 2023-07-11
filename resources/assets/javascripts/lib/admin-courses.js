const AdminCourses = {
    App: null,
    changeFiltersDependendOnInstitute(institut_id) {
        STUDIP.AdminCourses.App.changeFilter({ institut_id });
        //change Studiengangteil filter
        $.get(
            STUDIP.URLHelper.getURL('dispatch.php/admin/courses/get_stdgangteil_selector/' + institut_id)
        ).done((widget) => {
            $('select[name=stgteil_select]').closest('.sidebar-widget').replaceWith(widget);
        });

        //change Dozenten-Filter
        $.get(
            STUDIP.URLHelper.getURL('dispatch.php/admin/courses/get_teacher_selector/' + institut_id)
        ).done((widget) => {
            $('select[name=teacher_filter]').closest('.sidebar-widget').replaceWith(widget);
        });
    }
};
export default AdminCourses;
