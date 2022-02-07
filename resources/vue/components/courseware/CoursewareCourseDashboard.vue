<template>
    <div class="cw-dashboard-wrapper">
        <div v-if="defaultView" class="cw-dashboard cw-dashboard-default-view">
            <courseware-collapsible-box :title="$gettext('Überblick')" :open="true" class="cw-dashboard-box cw-dashboard-box-full">
                <div class="cw-dashboard-overview">
                    <courseware-oblong :name="textChapterFinished" icon="accept" size="small">
                        <template v-slot:oblongValue> {{ chapterCounter.finished }} </template>
                    </courseware-oblong>
                    <courseware-oblong :name="textChapterStarted" icon="play" size="small">
                        <template v-slot:oblongValue> {{ chapterCounter.started }} </template>
                    </courseware-oblong>
                    <courseware-oblong :name="textChapterAhead" icon="timetable" size="small">
                        <template v-slot:oblongValue> {{ chapterCounter.ahead }} </template>
                    </courseware-oblong>
                </div>
            </courseware-collapsible-box>
            <courseware-collapsible-box :title="$gettext('Fortschritt')" :open="true" class="cw-dashboard-box cw-dashboard-box-half">
                <courseware-dashboard-progress />
            </courseware-collapsible-box>
            <courseware-collapsible-box :title="$gettext('Aktivitäten')" :open="true" class="cw-dashboard-box cw-dashboard-box-half cw-content-loading">
                <courseware-dashboard-activities />
            </courseware-collapsible-box>
            <courseware-collapsible-box :title="$gettext('Aufgaben')" :open="true" class="cw-dashboard-box cw-dashboard-box-full">
                <courseware-dashboard-tasks v-if="!userIsTeacher && teacherStatusLoaded"/>
                <courseware-dashboard-students v-if="userIsTeacher && teacherStatusLoaded" />
            </courseware-collapsible-box>
        </div>
        <div v-if="taskView" class="cw-dashboard cw-dashboard-task-view">
            <courseware-dashboard-tasks v-if="!userIsTeacher && teacherStatusLoaded"/>
            <courseware-dashboard-students v-if="userIsTeacher && teacherStatusLoaded" />
        </div>
        <div v-if="activityView" class="cw-dashboard cw-dashboard-activity-view">
            <courseware-collapsible-box :title="$gettext('Aktivitäten')" :open="true" class="cw-dashboard-box cw-dashboard-box-full cw-content-loading">
                <courseware-dashboard-activities />
            </courseware-collapsible-box>
        </div>
        <courseware-companion-overlay />
    </div>
</template>

<script>
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareDashboardProgress from './CoursewareDashboardProgress.vue';
import CoursewareDashboardActivities from './CoursewareDashboardActivities.vue';
import CoursewareDashboardTasks from './CoursewareDashboardTasks.vue'
import CoursewareDashboardStudents from './CoursewareDashboardStudents.vue'
import CoursewareOblong from './CoursewareOblong.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-course-dashboard',
    components: {
        CoursewareCollapsibleBox,
        CoursewareOblong,
        CoursewareDashboardProgress,
        CoursewareDashboardActivities,
        CoursewareDashboardTasks,
        CoursewareDashboardStudents,
        CoursewareCompanionOverlay
    },
    data() {
        return {
            textChapterAhead: this.$gettext('bevorstehende Seiten'),
            textChapterStarted: this.$gettext('angefangene Seiten'),
            textChapterFinished: this.$gettext('abgeschlossene Seiten'),
        };
    },
    computed: {
        ...mapGetters({
            dashboardViewMode: 'dashboardViewMode',
            getCourseById: 'courses/byId',
            getStructuralElementById: 'courseware-structural-elements/byId',
            getUserById: 'users/byId',
            teacherStatusLoaded: 'teacherStatusLoaded',
            userId: 'userId',
            userIsTeacher: 'userIsTeacher',
        }),
        chapterCounter() {
            return STUDIP.courseware_chapter_counter;
        },
        defaultView() {
            return this.dashboardViewMode === 'default';
        },
        taskView() {
            return this.dashboardViewMode === 'task';
        },
        activityView() {
            return this.dashboardViewMode === 'activity';
        },
    }
};
</script>
