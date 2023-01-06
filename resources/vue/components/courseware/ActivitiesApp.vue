<template>
    <div class="cw-activities-wrapper">
        <courseware-activities />
        <MountingPortal mountTo="#courseware-activities-widget-filter-type" name="sidebar-filter-type">
            <courseware-activities-widget-filter-type />
        </MountingPortal>
        <MountingPortal mountTo="#courseware-activities-widget-filter-unit" name="sidebar-filter-unit">
            <courseware-activities-widget-filter-unit />
        </MountingPortal>
    </div>
</template>

<script>
import CoursewareActivities from './CoursewareActivities.vue';
import CoursewareActivitiesWidgetFilterType from './CoursewareActivitiesWidgetFilterType.vue';
import CoursewareActivitiesWidgetFilterUnit from './CoursewareActivitiesWidgetFilterUnit.vue';
import { mapGetters } from 'vuex';

export default {
    components: {
        CoursewareActivities,
        CoursewareActivitiesWidgetFilterType,
        CoursewareActivitiesWidgetFilterUnit
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher',
        }),
    },
}
</script>