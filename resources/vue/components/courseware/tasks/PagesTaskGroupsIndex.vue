<template>
    <div class="cw-tasks-wrapper">
        <div class="cw-tasks-list">
            <CoursewareDashboardStudents v-if="userIsTeacher" />
            <CoursewareDashboardTasks v-else />
        </div>
        <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions" v-if="userIsTeacher">
            <CoursewareTasksActionWidget />
        </MountingPortal>
        <courseware-companion-overlay />
    </div>
</template>

<script>
import CoursewareTasksActionWidget from '../widgets/CoursewareTasksActionWidget.vue';
import CoursewareDashboardTasks from './CoursewareDashboardTasks.vue';
import CoursewareDashboardStudents from './CoursewareDashboardStudents.vue';
import CoursewareCompanionOverlay from '../layouts/CoursewareCompanionOverlay.vue';
import { mapGetters } from 'vuex';

export default {
    components: {
        CoursewareTasksActionWidget,
        CoursewareDashboardTasks,
        CoursewareDashboardStudents,
        CoursewareCompanionOverlay,
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher',
        }),
    },
};
</script>
