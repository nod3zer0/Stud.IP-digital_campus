<template>
    <div class="cw-dashboard-tasks-wrapper">
        <table v-if="tasks.length > 0" class="default">
            <colgroup>
                <col />
            </colgroup>
            <thead>
                <tr>
                    <th>{{ $gettext('Status') }}</th>
                    <th>{{ $gettext('Aufgabe') }}</th>
                    <th>{{ $gettext('bearbeitet') }}</th>
                    <th>{{ $gettext('Abgabefrist') }}</th>
                    <th>{{ $gettext('Abgabe') }}</th>
                    <th class="responsive-hidden">{{ $gettext('Verlängerungsanfrage') }}</th>
                    <th class="responsive-hidden">{{ $gettext('Anmerkung') }}</th>
                    <th class="actions">{{ $gettext('Aktionen') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="{ task, taskGroup, status, element, feedback } in tasks" :key="task.id">
                    <td>
                        <studip-icon
                            v-if="status.shape !== undefined"
                            :shape="status.shape"
                            :role="status.role"
                            :title="status.description"
                        />
                    </td>
                    <td>
                        <a :href="getLinkToElement(element)">
                            <studip-icon
                                v-if="task.attributes['solver-type'] === 'group'"
                                shape="group2"
                                :title="$gettext('Gruppenaufgabe')"
                            />
                            {{ taskGroup.attributes.title }}
                        </a>
                    </td>
                    <td>{{ task.attributes?.progress?.toFixed(0) || '-' }}%</td>
                    <td>{{ getReadableDate(task.attributes['submission-date']) }}</td>
                    <td>
                        <studip-icon v-if="task.attributes.submitted" shape="accept" role="status-green" />
                    </td>
                    <td class="responsive-hidden">
                        <span v-show="task.attributes.renewal === 'declined'">
                            <studip-icon shape="decline" role="status-red" />
                            {{ $gettext('Anfrage abgelehnt') }}
                        </span>
                        <span v-show="task.attributes.renewal === 'pending'">
                            <studip-icon shape="date" role="status-yellow" />
                            {{ $gettext('Anfrage wird bearbeitet') }}
                        </span>
                        <span v-show="task.attributes.renewal === 'granted'">
                            {{ $gettext('verlängert bis') }}: {{ getReadableDate(task.attributes['renewal-date']) }}
                        </span>
                    </td>
                    <td class="responsive-hidden">
                        <studip-icon
                            v-if="feedback"
                            :title="$gettext('Anmerkung anzeigen')"
                            class="display-feedback"
                            shape="consultation"
                            role="clickable"
                            @click="displayFeedback(feedback)"
                        />
                    </td>
                    <td class="actions">
                        <studip-action-menu
                            :items="getTaskMenuItems(task, status, element)"
                            @submitTask="displaySubmitDialog(task)"
                            @renewalRequest="renewalRequest(task)"
                            @copyContent="copyContent(element)"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-else>
            <courseware-companion-box
                mood="sad"
                :msgCompanion="$gettext('Es wurden bisher keine Aufgaben gestellt.')"
            />
        </div>
        <studip-dialog
            v-if="showFeedbackDialog"
            :message="currentTaskFeedback"
            :title="text.feedbackDialog.title"
            @close="
                showFeedbackDialog = false;
                currentTaskFeedback = '';
            "
        />
        <studip-dialog
            v-if="showSubmitDialog"
            :title="text.submitDialog.title"
            :question="text.submitDialog.question"
            height="200"
            width="420"
            @confirm="submitTask"
            @close="closeSubmitDialog"
        />
    </div>
</template>
<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import StudipIcon from '../../StudipIcon.vue';
import StudipActionMenu from '../../StudipActionMenu.vue';
import StudipDialog from '../../StudipDialog.vue';
import taskHelperMixin from '../../../mixins/courseware/task-helper.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-dashboard-tasks',
    mixins: [taskHelperMixin],
    components: {
        CoursewareCompanionBox,
        StudipIcon,
        StudipActionMenu,
        StudipDialog,
    },
    data() {
        return {
            showFeedbackDialog: false,
            showSubmitDialog: false,
            currentTask: null,
            currentTaskFeedback: '',
            text: {
                feedbackDialog: {
                    title: this.$gettext('Anmerkung'),
                },
                submitDialog: {
                    title: this.$gettext('Aufgabe abgeben'),
                    question: this.$gettext(
                        'Änderungen sind nach Abgabe nicht mehr möglich. Möchten Sie diese Aufgabe jetzt wirklich abgeben?'
                    ),
                },
            },
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            allTasks: 'courseware-tasks/all',
            userId: 'userId',
            userById: 'users/byId',
            statusGroupById: 'status-groups/byId',
            getElementById: 'courseware-structural-elements/byId',
            getFeedbackById: 'courseware-task-feedback/byId',
            getTaskGroupById: 'courseware-task-groups/byId',
        }),
        tasks() {
            return this.allTasks.map((task) => {
                const result = {
                    task,
                    taskGroup: this.getTaskGroupById({ id: task.relationships['task-group'].data.id }),
                    status: this.getStatus(task),
                    element: this.getElementById({ id: task.relationships['structural-element'].data.id }),
                    feedback: null,
                };
                const feedbackId = task.relationships['task-feedback'].data?.id;
                if (feedbackId) {
                    result.feedback = this.getFeedbackById({ id: feedbackId });
                }

                return result;
            });
        },
    },
    methods: {
        ...mapActions({
            updateTask: 'updateTask',
            loadRemoteCoursewareStructure: 'loadRemoteCoursewareStructure',
            copyStructuralElement: 'copyStructuralElement',
            companionSuccess: 'companionSuccess',
            companionError: 'companionError',
        }),
        getTaskMenuItems(task, status, element) {
            let menuItems = [];
            if (!task.attributes.submitted && status.canSubmit) {
                menuItems.push({
                    id: 1,
                    label: this.$gettext('Aufgabe bearbeiten'),
                    icon: 'edit',
                    url: this.getLinkToElement(element),
                });
                menuItems.push({ id: 2, label: this.$gettext('Aufgabe abgeben'), icon: 'service', emit: 'submitTask' });
            }

            if (!task.attributes.submitted && !task.attributes.renewal) {
                menuItems.push({
                    id: 3,
                    label: this.$gettext('Verlängerung beantragen'),
                    icon: 'date',
                    emit: 'renewalRequest',
                });
            }
            if (task.attributes.submitted) {
                menuItems.push({ id: 4, label: this.$gettext('Inhalt kopieren'), icon: 'export', emit: 'copyContent' });
            }

            return menuItems;
        },
        async renewalRequest(task) {
            let attributes = task.attributes;
            attributes.renewal = 'pending';
            await this.updateTask({
                attributes: attributes,
                taskId: task.id,
            });
            this.companionSuccess({
                info: this.$gettext('Ihre Anfrage wurde eingereicht.'),
            });
        },
        displaySubmitDialog(task) {
            this.showSubmitDialog = true;
            this.currentTask = task;
        },
        closeSubmitDialog() {
            this.showSubmitDialog = false;
            this.currentTask = null;
        },
        async submitTask() {
            this.showSubmitDialog = false;
            let attributes = {};
            attributes.submitted = true;
            await this.updateTask({
                attributes: attributes,
                taskId: this.currentTask.id,
            });
            this.companionSuccess({
                info: '"' + this.currentTask.attributes.title + '" ' + this.$gettext('wurde erfolgreich abgegeben.'),
            });
            this.currentTask = null;
        },
        async copyContent(element) {
            let ownCoursewareInstance = await this.loadRemoteCoursewareStructure({
                rangeId: this.userId,
                rangeType: 'users',
            });
            if (ownCoursewareInstance !== null) {
                await this.copyStructuralElement({
                    parentId: ownCoursewareInstance.relationships.root.data.id,
                    elementId: element.id,
                    removeType: true,
                    migrate: false,
                });
                this.companionSuccess({
                    info: this.$gettext('Die Inhalte wurden zu Ihren persönlichen Lernmaterialien hinzugefügt.'),
                });
            } else {
                this.companionError({
                    info: this.$gettext(
                        'Die Inhalte konnten nicht zu Ihren persönlichen Lernmaterialien hinzugefügt werden.'
                    ),
                });
            }
        },
        displayFeedback(feedback) {
            this.showFeedbackDialog = true;
            this.currentTaskFeedback = feedback.attributes.content;
        },
    },
};
</script>
