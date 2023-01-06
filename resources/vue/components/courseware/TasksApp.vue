<template>
    <div class="cw-tasks-wrapper">
        <div class="cw-tasks-list">
            <courseware-dashboard-students v-if="userIsTeacher" />
            <courseware-dashboard-tasks v-else />
        </div>
        <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions" v-if="userIsTeacher">
            <courseware-tasks-action-widget />
        </MountingPortal>
    </div>
</template>

<script>
import CoursewareTasksActionWidget from './CoursewareTasksActionWidget.vue';
import CoursewareDashboardTasks from './CoursewareDashboardTasks.vue'
import CoursewareDashboardStudents from './CoursewareDashboardStudents.vue'
import { mapGetters } from 'vuex';

export default {
    components: {
        CoursewareTasksActionWidget,
        CoursewareDashboardTasks,
        CoursewareDashboardStudents,
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher',
        }),
    },
};
</script>
