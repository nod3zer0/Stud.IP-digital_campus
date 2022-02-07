<template>
    <div class="cw-dashboard-wrapper">
        <courseware-course-dashboard></courseware-course-dashboard>
        <MountingPortal mountTo="#courseware-dashboard-view-widget" name="sidebar-views">
            <courseware-dashboard-view-widget></courseware-dashboard-view-widget>
        </MountingPortal>
    </div>
</template>

<script>
import CoursewareCourseDashboard from './CoursewareCourseDashboard.vue';
import CoursewareDashboardViewWidget from './CoursewareDashboardViewWidget.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareCourseDashboard,
        CoursewareDashboardViewWidget
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
        }),
    },
    methods: {
        ...mapActions({
            loadCoursewareStructure: 'courseware-structure/load',
            loadTeacherStatus: 'loadTeacherStatus',
        }),
    },
    async mounted() {
        await this.loadCoursewareStructure();
        await this.loadTeacherStatus(this.userId);
    }
};
</script>
