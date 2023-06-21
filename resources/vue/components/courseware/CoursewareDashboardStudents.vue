<template>
    <div class="cw-dashboard-students-wrapper">
        <table v-if="tasks.length > 0" class="default">
            <colgroup>
                <col />
            </colgroup>
            <thead>
                <tr>
                    <th><translate>Status</translate></th>
                    <th><translate>Aufgabentitel</translate></th>
                    <th><translate>Teilnehmende/Gruppen</translate></th>
                    <th><translate class="responsive-hidden">Seite</translate></th>
                    <th><translate>bearbeitet</translate></th>
                    <th><translate>Abgabefrist</translate></th>
                    <th><translate>Abgabe</translate></th>
                    <th class="responsive-hidden renewal"><translate>Verlängerungsanfrage</translate></th>
                    <th class="responsive-hidden feedback"><translate>Feedback</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="{ task, taskGroup, status, element, user, group, feedback } in tasks" :key="task.id">
                    <td>
                        <studip-icon
                            v-if="status.shape !== undefined"
                            :shape="status.shape"
                            :role="status.role"
                            :title="status.description"
                            aria-hidden="true"
                        />
                        <span class="sr-only">{{ status.description }}</span>
                    </td>
                    <td>
                        {{ taskGroup && taskGroup.attributes.title }}
                    </td>
                    <td>
                        <span v-if="user">
                            <studip-icon 
                                shape="person2"
                                role="info"
                                aria-hidden="true"
                                :title="$gettext('Teilnehmende Person')" 
                            />
                            <span class="sr-only">{{ $gettext('Teilnehmende Person') }}</span>
                            {{ user.attributes['formatted-name'] }}

                        </span>
                        <span v-if="group">
                            <studip-icon
                                shape="group2"
                                role="info"
                                aria-hidden="true"
                                :title="$gettext('Gruppe')"
                            />
                            <span class="sr-only">{{ $gettext('Gruppe') }}</span>
                            {{ group.attributes['name'] }}

                        </span>
                    </td>
                    <td class="responsive-hidden">
                        <a v-if="task.attributes.submitted" :href="getLinkToElement(element)">
                            {{ element.attributes.title }}
                        </a>
                        <span v-else>{{ element.attributes.title }}</span>
                    </td>
                    <td>{{ task.attributes?.progress?.toFixed(2) || '-.--' }}%</td>
                    <td>{{ getReadableDate(task.attributes['submission-date']) }}</td>
                    <td>
                        <studip-icon v-if="task.attributes.submitted" shape="accept" role="status-green" />
                    </td>
                    <td class="responsive-hidden">
                        <button
                            v-show="task.attributes.renewal === 'pending'"
                            class="button"
                            @click="solveRenewalRequest(task)"
                        >
                            <translate>Anfrage bearbeiten</translate>
                        </button>
                        <span v-show="task.attributes.renewal === 'declined'">
                            <studip-icon shape="decline" role="status-red" />
                            <translate>Anfrage abgelehnt</translate>
                        </span>
                        <span v-show="task.attributes.renewal === 'granted'">
                            <translate>verlängert bis</translate>:
                            {{ getReadableDate(task.attributes['renewal-date']) }}
                        </span>
                        <studip-icon
                            v-if="task.attributes.renewal === 'declined' || task.attributes.renewal === 'granted'"
                            :title="$gettext('Anfrage bearbeiten')"
                            class="edit"
                            shape="edit"
                            role="clickable"
                            @click="solveRenewalRequest(task)"
                        />
                    </td>
                    <td class="responsive-hidden">
                        <span
                            v-if="feedback"
                            :title="
                                $gettext('Feedback geschrieben am:') +
                                ' ' +
                                getReadableDate(feedback.attributes['chdate'])
                            "
                        >
                            <studip-icon shape="accept" role="status-green" />
                            <translate>Feedback gegeben</translate>
                            <studip-icon
                                :title="$gettext('Feedback bearbeiten')"
                                class="edit"
                                shape="edit"
                                role="clickable"
                                @click="editFeedback(feedback)"
                            />
                        </span>

                        <button
                            v-show="!feedback && task.attributes.submitted"
                            class="button"
                            @click="addFeedback(task)"
                        >
                            <translate>Feedback geben</translate>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-else>
            <courseware-companion-box 
                mood="pointing"
                :msgCompanion="$gettext('Es wurden bisher keine Aufgaben gestellt.')"
            >
            </courseware-companion-box>
        </div>
        <studip-dialog
            v-if="showRenewalDialog"
            :title="text.renewalDialog.title"
            :confirmText="text.renewalDialog.confirm"
            confirmClass="accept"
            :closeText="text.renewalDialog.close"
            closeClass="cancel"
            height="350"
            @close="
                showRenewalDialog = false;
                currentDialogTask = {};
            "
            @confirm="updateRenewal"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Fristverlängerung</translate>
                        <select v-model="currentDialogTask.attributes.renewal">
                            <option value="declined">
                                <translate>ablehnen</translate>
                            </option>
                            <option value="granted">
                                <translate>gewähren</translate>
                            </option>
                        </select>
                    </label>
                    <label v-if="currentDialogTask.attributes.renewal === 'granted'">
                        <translate>neue Frist</translate>
                        <courseware-date-input v-model="currentDialogTask.attributes['renewal-date']" class="size-l" />
                    </label>
                </form>
            </template>
        </studip-dialog>
        <studip-dialog
            v-if="showEditFeedbackDialog"
            :title="text.editFeedbackDialog.title"
            :confirmText="text.editFeedbackDialog.confirm"
            confirmClass="accept"
            :closeText="text.editFeedbackDialog.close"
            closeClass="cancel"
            height="420"
            @close="
                showEditFeedbackDialog = false;
                currentDialogFeedback = {};
            "
            @confirm="updateFeedback"
        >
            <template v-slot:dialogContent>
                <courseware-companion-box
                    v-if="currentDialogFeedback.attributes.content === ''"
                    mood="pointing"
                    :msgCompanion="
                        $gettext('Sie haben kein Feedback geschrieben, beim Speichern wird dieses Feedback gelöscht!')
                    "
                />
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Feedback</translate>
                        <textarea v-model="currentDialogFeedback.attributes.content" />
                    </label>
                </form>
            </template>
        </studip-dialog>
        <studip-dialog
            v-if="showAddFeedbackDialog"
            :title="text.addFeedbackDialog.title"
            :confirmText="text.addFeedbackDialog.confirm"
            confirmClass="accept"
            :closeText="text.addFeedbackDialog.close"
            closeClass="cancel"
            @close="
                showAddFeedbackDialog = false;
                currentDialogFeedback = {};
            "
            @confirm="createFeedback"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Feedback</translate>
                        <textarea v-model="currentDialogFeedback.attributes.content" />
                    </label>
                </form>
            </template>
        </studip-dialog>
        <courseware-tasks-dialog-distribute v-if="showTasksDistributeDialog" @newtask="reloadTasks"/>
    </div>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';
import StudipDialog from './../StudipDialog.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareDateInput from './CoursewareDateInput.vue';
import CoursewareTasksDialogDistribute from './CoursewareTasksDialogDistribute.vue';
import taskHelperMixin from '../../mixins/courseware/task-helper.js';
import { mapActions, mapGetters } from 'vuex';


export default {
    name: 'courseware-dashboard-students',
    mixins: [taskHelperMixin],
    components: {
        CoursewareCompanionBox,
        CoursewareDateInput,
        StudipIcon,
        StudipDialog,
        CoursewareTasksDialogDistribute,
    },
    data() {
        return {
            showRenewalDialog: false,
            showAddFeedbackDialog: false,
            showEditFeedbackDialog: false,
            currentDialogTask: {},
            currentDialogFeedback: {},
            text: {
                renewalDialog: {
                    title: this.$gettext('Verlängerungsanfrage bearbeiten'),
                    confirm: this.$gettext('Speichern'),
                    close: this.$gettext('Schließen'),
                },
                editFeedbackDialog: {
                    title: this.$gettext('Feedback zur Aufgabe ändern'),
                    confirm: this.$gettext('Speichern'),
                    close: this.$gettext('Schließen'),
                },
                addFeedbackDialog: {
                    title: this.$gettext('Feedback zur Aufgabe geben'),
                    confirm: this.$gettext('Speichern'),
                    close: this.$gettext('Schließen'),
                },
            },
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            allTasks: 'courseware-tasks/all',
            userById: 'users/byId',
            statusGroupById: 'status-groups/byId',
            getElementById: 'courseware-structural-elements/byId',
            getFeedbackById: 'courseware-task-feedback/byId',
            relatedTaskGroups: 'courseware-task-groups/related',
            showTasksDistributeDialog: 'showTasksDistributeDialog'
        }),
        tasks() {
            return this.allTasks.map((task) => {
                const result = {
                    task,
                    taskGroup: this.relatedTaskGroups({ parent: task, relationship: 'task-group' }),
                    status: this.getStatus(task),
                    element: this.getElementById({ id: task.relationships['structural-element'].data.id }),
                    user: null,
                    group: null,
                    feedback: null,
                };
                let solver = task.relationships.solver.data;
                if (solver.type === 'users') {
                    result.user = this.userById({ id: solver.id });
                }
                if (solver.type === 'status-groups') {
                    result.group = this.statusGroupById({ id: solver.id });
                }

                const feedbackId = task.relationships['task-feedback'].data?.id;
                if (feedbackId) {
                    result.feedback = this.getFeedbackById({ id: feedbackId });
                }

                return result;
            });
        },
        managerUrl() {
            return STUDIP.URLHelper.getURL('dispatch.php/course/courseware/manager', {cid: this.context.id});
        }
    },
    methods: {
        ...mapActions({
            updateTask: 'updateTask',
            createTaskFeedback: 'createTaskFeedback',
            updateTaskFeedback: 'updateTaskFeedback',
            deleteTaskFeedback: 'deleteTaskFeedback',
            loadRemoteCoursewareStructure: 'loadRemoteCoursewareStructure',
            copyStructuralElement: 'copyStructuralElement',
            companionSuccess: 'companionSuccess',
            companionError: 'companionError',
            loadAllTasks: 'courseware-tasks/loadAll'
        }),
        addFeedback(task) {
            this.currentDialogFeedback.attributes = {};
            this.currentDialogFeedback.attributes.content = '';
            this.currentDialogFeedback.relationships = {};
            this.currentDialogFeedback.relationships.task = {};
            this.currentDialogFeedback.relationships.task.data = {};
            this.currentDialogFeedback.relationships.task.data.id = task.id;
            this.currentDialogFeedback.relationships.task.data.type = task.type;
            this.showAddFeedbackDialog = true;
        },
        createFeedback() {
            if (this.currentDialogFeedback.attributes.content === '') {
                this.companionError({
                    info: this.$gettext('Bitte schreiben Sie ein Feedback.'),
                });
                return false;
            }
            this.showAddFeedbackDialog = false;
            this.createTaskFeedback({
                taskFeedback: this.currentDialogFeedback,
            });
            this.currentDialogFeedback = {};
        },
        editFeedback(feedback) {
            this.currentDialogFeedback = _.cloneDeep(feedback);
            this.showEditFeedbackDialog = true;
        },
        async updateFeedback() {
            this.showEditFeedbackDialog = false;
            let attributes = {};
            attributes.content = this.currentDialogFeedback.attributes.content;
            if (attributes.content === '') {
                await this.deleteTaskFeedback({
                    taskFeedbackId: this.currentDialogFeedback.id,
                });
                this.companionSuccess({
                    info: this.$gettext('Feedback wurde gelöscht.'),
                });
            } else {
                await this.updateTaskFeedback({
                    attributes: attributes,
                    taskFeedbackId: this.currentDialogFeedback.id,
                });
                this.companionSuccess({
                    info: this.$gettext('Feedback wurde gespeichert.'),
                });
            }

            this.currentDialogFeedback = {};
        },
        solveRenewalRequest(task) {
            this.currentDialogTask = _.cloneDeep(task);
            this.currentDialogTask.attributes['renewal-date'] = new Date().toISOString();
            this.showRenewalDialog = true;
        },
        updateRenewal() {
            this.showRenewalDialog = false;
            let attributes = {};
            attributes.renewal = this.currentDialogTask.attributes.renewal;
            if (attributes.renewal === 'granted') {
                attributes['renewal-date'] = new Date(this.currentDialogTask.attributes['renewal-date'] || Date.now()).toISOString();
            }

            this.updateTask({
                attributes: attributes,
                taskId: this.currentDialogTask.id,
            });
            this.currentDialogTask = {};
        },
        reloadTasks() {
            this.loadAllTasks({ 
                options: {
                    'filter[cid]': this.context.id,
                    include: 'solver, structural-element, task-feedback, task-group, task-group.lecturer'
                }
            });
        }
    },
};
</script>
