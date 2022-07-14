<template>
    <ul class="widget-list widget-links sidebar-views cw-view-widget">
        <li :class="{ active: defaultView }">
            <a href="#" @click.prevent="setDefaultView">
                <translate>Standard</translate>
            </a>
        </li>
        <li :class="{ active: taskView }">
            <a href="#" @click.prevent="setTaskView">
                <translate>Aufgaben</translate>
            </a>
        </li>
        <li :class="{ active: activityView }">
            <a href="#" @click.prevent="setActivityView">
                <translate>Aktivitäten</translate>
            </a>
        </li>
    </ul>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-dashboard-view-widget',
    computed: {
        ...mapGetters({
            dashboardViewMode: 'dashboardViewMode',
            context: 'context',
        }),
        defaultView() {
            return this.dashboardViewMode === 'default';
        },
        taskView() {
            return this.dashboardViewMode === 'task';
        },
        activityView() {
            return this.dashboardViewMode === 'activity';
        },
    },
    methods: {
        ...mapActions({
            setDashboardViewMode: 'setDashboardViewMode'
        }),
        setDefaultView() {
            this.setDashboardViewMode('default');
        },
        setTaskView() {
            this.setDashboardViewMode('task');
        },
        setActivityView() {
            this.setDashboardViewMode('activity');
        },
    },
};
</script>
