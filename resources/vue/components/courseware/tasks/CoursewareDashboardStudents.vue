<template>
    <div class="cw-dashboard-students-wrapper">
        <CoursewareRibbon :isContentBar="true" :showToolbarButton="false">
            <template #buttons>
                <router-link :to="{ name: 'task-groups-index' }">
                    <StudipIcon shape="category-task" :size="24" />
                </router-link>
            </template>
            <template #breadcrumbList>
                <li>
                    {{ $gettext('Aufgaben') }}
                </li>
            </template>
        </CoursewareRibbon>
        <table class="default" v-if="taskGroups.length">
            <thead>
                <tr class="sortable">
                    <th>
                        {{ $gettext('Status') }}
                    </th>
                    <th :class="getSortClass('task-group-title')" @click="sort('task-group-title')">
                        {{ $gettext('Titel') }}
                    </th>
                    <th :class="getSortClass('end-date')" @click="sort('end-date')">
                        {{ $gettext('Bearbeitungszeit') }}
                    </th>
                    <th class="actions">{{ $gettext('Aktionen') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(taskGroup, index) in sortedTaskGroups" :key="index">
                    <td>
                        <StudipIcon
                            :shape="status(taskGroup).shape"
                            :role="status(taskGroup).role"
                            :title="status(taskGroup).description"
                            aria-hidden="true"
                        />
                        <span class="sr-only">{{ status(taskGroup).description }}</span>
                    </td>
                    <td>
                        <router-link :to="{ name: 'task-groups-show', params: { id: taskGroup.id } }">{{
                            taskGroup.attributes.title
                        }}</router-link>
                    </td>
                    <td>
                        <StudipDate :date="new Date(taskGroup.attributes['start-date'])" /> - <StudipDate
                            :date="new Date(taskGroup.attributes['end-date'])"
                        />
                    </td>
                    <td class="actions">
                        <StudipActionMenu
                            :items="getTaskGroupMenuItems(taskGroup)"
                            @addsolvers="onShowAddSolvers(taskGroup)"
                            @deadline="onShowModifyDeadline(taskGroup)"
                            @delete="onShowDeleteDialog(taskGroup)"
                        />
                    </td>
                </tr>
            </tbody>
        </table>

        <CompanionBox v-else-if="!tasksLoading" :msgCompanion="$gettext('Es wurden noch keine Aufgaben verteilt.')">
            <template #companionActions>
                <button @click="setShowTasksDistributeDialog(true)" type="button" class="button">
                    {{ $gettext('Aufgabe verteilen') }}
                </button>
            </template>
        </CompanionBox>

        <TaskGroupsAddSolversDialog v-if="showTaskGroupsAddSolversDialog" :taskGroup="selectedTaskGroup" @newtask="reloadTasks" />
        <TaskGroupsDeleteDialog v-if="showTaskGroupsDeleteDialog" :taskGroup="selectedTaskGroup" />
        <TaskGroupsModifyDeadlineDialog v-if="showTaskGroupsModifyDeadlineDialog" :taskGroup="selectedTaskGroup" />
        <CoursewareTasksDialogDistribute v-if="showTasksDistributeDialog" @newtask="reloadTasks" />
    </div>
</template>

<script>
import _ from 'lodash';
import { mapActions, mapGetters } from 'vuex';
import CompanionBox from '../layouts/CoursewareCompanionBox.vue';
import CoursewareRibbon from '../structural-element/CoursewareRibbon.vue';
import CoursewareTasksDialogDistribute from './CoursewareTasksDialogDistribute.vue';
import StudipActionMenu from '../../StudipActionMenu.vue';
import StudipDate from '../../StudipDate.vue';
import StudipIcon from '../../StudipIcon.vue';
import TaskGroupsAddSolversDialog from './TaskGroupsAddSolversDialog.vue';
import TaskGroupsDeleteDialog from './TaskGroupsDeleteDialog.vue';
import TaskGroupsModifyDeadlineDialog from './TaskGroupsModifyDeadlineDialog.vue';
import { getStatus } from './task-groups-helper.js';

export default {
    name: 'courseware-dashboard-students',
    components: {
        CompanionBox,
        CoursewareRibbon,
        CoursewareTasksDialogDistribute,
        StudipActionMenu,
        StudipDate,
        StudipIcon,
        TaskGroupsAddSolversDialog,
        TaskGroupsDeleteDialog,
        TaskGroupsModifyDeadlineDialog,
    },
    data: () => ({
        selectedTaskGroup: null,
        sortBy: 'end-date',
        sortAsc: false,
    }),
    computed: {
        ...mapGetters({
            context: 'context',
            showTaskGroupsAddSolversDialog: 'tasks/showTaskGroupsAddSolversDialog',
            showTaskGroupsDeleteDialog: 'tasks/showTaskGroupsDeleteDialog',
            showTaskGroupsModifyDeadlineDialog: 'tasks/showTaskGroupsModifyDeadlineDialog',
            showTasksDistributeDialog: 'tasks/showTasksDistributeDialog',
            taskGroupsByCid: 'tasks/taskGroupsByCid',
            tasksLoading: 'courseware-tasks/isLoading',
        }),
        sortedTaskGroups() {
            const sorters = {
                'task-group-title': (taskGroup) => taskGroup.attributes.title,
                'end-date': (taskGroup) => new Date(taskGroup.attributes['end-date']),
            };

            return _.chain(this.taskGroups)
                .sortBy([sorters[this.sortBy]])
                .thru((sorted) => (this.sortAsc ? sorted : _.reverse(sorted)))
                .value();
        },
        taskGroups() {
            return this.taskGroupsByCid(this.context.id);
        },
    },
    methods: {
        ...mapActions({
            loadAllTasks: 'courseware-tasks/loadAll',
            setShowTaskGroupsAddSolversDialog: 'tasks/setShowTaskGroupsAddSolversDialog',
            setShowTaskGroupsDeleteDialog: 'tasks/setShowTaskGroupsDeleteDialog',
            setShowTaskGroupsModifyDeadlineDialog: 'tasks/setShowTaskGroupsModifyDeadlineDialog',
            setShowTasksDistributeDialog: 'tasks/setShowTasksDistributeDialog',
        }),
        getSortClass(col) {
            if (col === this.sortBy) {
                return this.sortAsc ? 'sortasc' : 'sortdesc';
            }
            return '';
        },
        getTaskGroupMenuItems(taskGroup) {
            let menuItems = [];

            const isBeforeEndDate = new Date() < new Date(taskGroup.attributes['end-date']);
            if (isBeforeEndDate) {
                menuItems.push({
                    id: 'add-solvers',
                    label: this.$gettext('Teilnehmende hinzufügen'),
                    icon: 'add',
                    emit: 'addsolvers'
                });
                menuItems.push({
                    id: 'modify-deadline',
                    label: this.$gettext('Bearbeitungszeit verlängern'),
                    icon: 'date',
                    emit: 'deadline'
                });
            }

            menuItems.push({
                id: 'delete',
                label: this.$gettext('Aufgabe löschen'),
                icon: 'trash',
                emit: 'delete',
            });

            return menuItems;
        },
        onShowAddSolvers(taskGroup) {
            this.selectedTaskGroup = taskGroup;
            this.setShowTaskGroupsAddSolversDialog(true);
        },
        onShowDeleteDialog(taskGroup) {
            this.selectedTaskGroup = taskGroup;
            this.setShowTaskGroupsDeleteDialog(true);
        },
        onShowModifyDeadline(taskGroup) {
            this.selectedTaskGroup = taskGroup;
            this.setShowTaskGroupsModifyDeadlineDialog(true);
        },
        reloadTasks() {
            this.loadAllTasks({
                options: {
                    'filter[cid]': this.context.id,
                    include: 'solver, structural-element, task-feedback, task-group, task-group.lecturer',
                },
            });
        },
        sort(sortBy) {
            if (this.sortBy === sortBy) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortBy = sortBy;
            }
        },
        status: getStatus,
    },
};
</script>

<style scoped>
.cw-dashboard-students-wrapper >>> .cw-ribbon-nav {
    min-width: 24px;
    padding: 0 1em;
    height: 24px;
    margin-top: 2px;
}
th {
    cursor: pointer;
}
th:is(:first-child,:last-child) {
    cursor: not-allowed;
}
</style>
