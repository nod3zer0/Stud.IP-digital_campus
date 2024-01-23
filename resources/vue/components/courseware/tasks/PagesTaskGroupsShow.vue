<template>
    <div class="cw-tasks-wrapper">
        <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions" v-if="userIsTeacher">
            <CoursewareTasksActionWidget :taskGroup="taskGroup" />
        </MountingPortal>

        <div v-if="taskGroup" class="cw-tasks-list">
            <CoursewareRibbon :isContentBar="true" :showToolbarButton="false">
                <template #buttons>
                    <router-link :to="{ name: 'task-groups-index' }">
                        <StudipIcon shape="category-task" :size="24" />
                    </router-link>
                </template>
                <template #breadcrumbList>
                    <li>
                        <router-link :to="{ name: 'task-groups-index' }">
                            {{ $gettext('Aufgaben') }}
                        </router-link>
                    </li>
                    <li>{{ taskGroup.attributes['title'] }}</li>
                </template>
            </CoursewareRibbon>

            <TaskGroup
                :taskGroup="taskGroup"
                :tasks="tasksByGroup[taskGroup.id]"
                @add-feedback="onShowAddFeedback"
                @edit-feedback="onShowEditFeedback"
                @solve-renewal="onShowSolveRenewal"
            />
        </div>
        <CompanionBox
            v-else-if="!tasksLoading"
            :msgCompanion="$gettext('Diese Courseware-Aufgabe konnte nicht gefunden werden.')"
        />

        <AddFeedbackDialog
            v-if="showAddFeedbackDialog"
            :content="currentDialogFeedback.attributes.content"
            @create="createFeedback"
            @close="closeDialogs"
        />

        <EditFeedbackDialog
            v-if="showEditFeedbackDialog"
            :content="currentDialogFeedback.attributes.content"
            @update="updateFeedback"
            @close="closeDialogs"
        />

        <RenewalDialog
            v-if="renewalTask"
            :renewalDate="renewalDate"
            :renewalState="renewalTask.attributes.renewal"
            @update="updateRenewal"
            @close="closeDialogs"
        />

        <TaskGroupsAddSolversDialog v-if="showTaskGroupsAddSolversDialog" :taskGroup="taskGroup" @newtask="reloadTasks" />
        <TaskGroupsDeleteDialog v-if="showTaskGroupsDeleteDialog" :taskGroup="taskGroup" />
        <TaskGroupsModifyDeadlineDialog v-if="showTaskGroupsModifyDeadlineDialog" :taskGroup="taskGroup" />
        <CoursewareTasksDialogDistribute v-if="showTasksDistributeDialog" @newtask="reloadTasks" />
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import AddFeedbackDialog from './AddFeedbackDialog.vue';
import CompanionBox from '../layouts/CoursewareCompanionBox.vue';
import CoursewareRibbon from '../structural-element/CoursewareRibbon.vue';
import CoursewareTasksActionWidget from '../widgets/CoursewareTasksActionWidget.vue';
import CoursewareTasksDialogDistribute from './CoursewareTasksDialogDistribute.vue';
import EditFeedbackDialog from './EditFeedbackDialog.vue';
import RenewalDialog from './RenewalDialog.vue';
import TaskGroup from './TaskGroup.vue';
import TaskGroupsAddSolversDialog from './TaskGroupsAddSolversDialog.vue';
import TaskGroupsDeleteDialog from './TaskGroupsDeleteDialog.vue';
import TaskGroupsModifyDeadlineDialog from './TaskGroupsModifyDeadlineDialog.vue';

export default {
    components: {
        AddFeedbackDialog,
        CompanionBox,
        CoursewareRibbon,
        CoursewareTasksActionWidget,
        CoursewareTasksDialogDistribute,
        EditFeedbackDialog,
        RenewalDialog,
        TaskGroup,
        TaskGroupsAddSolversDialog,
        TaskGroupsDeleteDialog,
        TaskGroupsModifyDeadlineDialog,
    },
    props: ['id'],
    data() {
        return {
            currentDialogFeedback: {},
            renewalTask: null,
            showAddFeedbackDialog: false,
            showEditFeedbackDialog: false,
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            getTaskGroup: 'courseware-task-groups/byId',
            showTaskGroupsAddSolversDialog: 'tasks/showTaskGroupsAddSolversDialog',
            showTaskGroupsDeleteDialog: 'tasks/showTaskGroupsDeleteDialog',
            showTaskGroupsModifyDeadlineDialog: 'tasks/showTaskGroupsModifyDeadlineDialog',
            showTasksDistributeDialog: 'tasks/showTasksDistributeDialog',
            tasksByCid: 'tasks/tasksByCid',
            tasksLoading: 'courseware-tasks/isLoading',
            userIsTeacher: 'userIsTeacher',
        }),
        renewalDate() {
            return this.renewalTask ? new Date(this.renewalTask.attributes['renewal-date']) : new Date();
        },
        taskGroup() {
            return this.getTaskGroup({ id: this.id });
        },
        tasksByGroup() {
            return this.tasksByCid(this.context.id).reduce((memo, task) => {
                const key = task.relationships['task-group'].data.id;
                (memo[key] || (memo[key] = [])).push(task);

                return memo;
            }, {});
        },
    },
    methods: {
        ...mapActions({
            companionError: 'companionError',
            companionSuccess: 'companionSuccess',
            createTaskFeedback: 'createTaskFeedback',
            deleteTaskFeedback: 'deleteTaskFeedback',
            loadAllTasks: 'courseware-tasks/loadAll',
            loadTaskGroup: 'tasks/loadTaskGroup',
            updateTask: 'updateTask',
            updateTaskFeedback: 'updateTaskFeedback',
        }),
        closeDialogs() {
            this.showAddFeedbackDialog = false;
            this.showEditFeedbackDialog = false;

            this.currentDialogFeedback = {};
            this.renewalTask = null;
        },
        createFeedback({ content }) {
            if (content === '') {
                this.companionError({
                    info: this.$gettext('Bitte schreiben Sie ein Feedback.'),
                });
                return false;
            }
            this.currentDialogFeedback.attributes.content = content;
            this.createTaskFeedback({ taskFeedback: this.currentDialogFeedback });
            this.closeDialogs();
        },
        onShowAddFeedback(task) {
            this.currentDialogFeedback = {
                attributes: { content: '' },
                relationships: {
                    task: {
                        data: {
                            id: task.id,
                            type: task.type,
                        },
                    },
                },
            };
            this.showAddFeedbackDialog = true;
        },
        onShowEditFeedback(feedback) {
            this.currentDialogFeedback = _.cloneDeep(feedback);
            this.showEditFeedbackDialog = true;
        },
        onShowSolveRenewal(task) {
            this.renewalTask = _.cloneDeep(task);
            this.renewalTask.attributes['renewal-date'] = new Date().toISOString();
        },
        reloadTasks() {
            this.loadAllTasks({
                options: {
                    'filter[cid]': this.context.id,
                    include: 'solver, structural-element, task-feedback, task-group, task-group.lecturer',
                },
            });
        },
        updateRenewal({ state, date }) {
            const attributes = { renewal: state };
            if (date) {
                attributes['renewal-date'] = date.toISOString();
            }

            this.updateTask({ attributes, taskId: this.renewalTask.id });
            this.closeDialogs();
        },
        async updateFeedback({ content }) {
            if (content === '') {
                await this.deleteTaskFeedback({ taskFeedbackId: this.currentDialogFeedback.id });
                this.companionSuccess({ info: this.$gettext('Feedback wurde gelöscht.') });
            } else {
                await this.updateTaskFeedback({
                    attributes: { content },
                    taskFeedbackId: this.currentDialogFeedback.id,
                });
                this.companionSuccess({
                    info: this.$gettext('Feedback wurde gespeichert.'),
                });
            }
            this.closeDialogs();
        },
    },
};
</script>

<style scoped>
.cw-tasks-wrapper >>> .cw-ribbon-nav {
    min-width: 24px;
    padding: 0 1em;
    height: 24px;
    margin-top: 2px;
}
</style>
