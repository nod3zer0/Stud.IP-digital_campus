<template>
    <sidebar-widget id="courseware-action-widget" :title="$gettext('Aktionen')">
        <template #content>
            <ul class="widget-list widget-links cw-action-widget">
                <template v-if="taskGroup">
                    <li v-if="isBeforeEndDate" class="cw-action-widget-task-groups-deadline">
                        <button @click="modifyDeadline(taskGroup)">
                            {{ $gettext('Bearbeitungszeit verlängern') }}
                        </button>
                    </li>
                    <li v-if="isBeforeEndDate" class="cw-action-widget-task-groups-add-solvers">
                        <button @click="addSolvers(taskGroup)">
                            {{ $gettext('Teilnehmende hinzufügen') }}
                        </button>
                    </li>
                    <li class="cw-action-widget-task-groups-delete">
                        <button @click="deleteTaskGroup(taskGroup)">
                            {{ $gettext('Aufgabe löschen') }}
                        </button>
                    </li>
                </template>
                <li v-else class="cw-action-widget-add">
                    <button @click="setShowTasksDistributeDialog(true)">
                        {{ $gettext('Aufgabe verteilen') }}
                    </button>
                </li>
            </ul>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../../SidebarWidget.vue';

import { mapActions } from 'vuex';

export default {
    name: 'courseware-tasks-action-widget',
    components: {
        SidebarWidget,
    },
    props: ['taskGroup'],
    computed: {
        isBeforeEndDate() {
            return this.taskGroup && new Date() < new Date(this.taskGroup.attributes['end-date']);
        },
    },
    methods: {
        ...mapActions({
            addSolvers: 'tasks/setShowTaskGroupsAddSolversDialog',
            deleteTaskGroup: 'tasks/setShowTaskGroupsDeleteDialog',
            modifyDeadline: 'tasks/setShowTaskGroupsModifyDeadlineDialog',
            setShowTasksDistributeDialog: 'tasks/setShowTasksDistributeDialog',
        }),
    },
};
</script>

<style scoped>
.cw-action-widget-task-groups-add-solvers {
    background-image: url('../images/icons/blue/add.svg');
    background-size: 16px;
}
.cw-action-widget-task-groups-deadline {
    background-image: url('../images/icons/blue/date.svg');
    background-size: 16px;
}
.cw-action-widget-task-groups-delete {
    background-image: url('../images/icons/blue/trash.svg');
    background-size: 16px;
}
</style>
