<template>
    <div class="cw-manager-task-distributor">
        <form class="default" @submit.prevent="">
            <fieldset>
                <legend><translate>Aufgabe</translate></legend>
                <label>
                    <translate>Aufgabentitel</translate>
                    <input type="text" v-model="taskTitle" />
                </label>
                <label>
                    <translate>Aufgabenvorlage</translate>
                    <select v-model="selectedElementId">
                        <option value="" disabled>
                            <translate>wählen Sie eine Vorlage aus</translate>
                        </option>
                        <option v-for="template in taskTemplates" :key="template.id" :value="template.id">
                            {{ template.attributes.title }}
                        </option>
                    </select>
                </label>
                <label>
                    <translate>Abgabefrist</translate>
                    <input type="date" v-model="submissionDate" />
                </label>
                <label>
                    <translate>Inhalte ergänzen</translate>
                    <select class="size-s" v-model="solverMayAddBlocks">
                        <option value="true"><translate>ja</translate></option>
                        <option value="false"><translate>nein</translate></option>
                    </select>
                </label>
                <label>
                    <translate>Typ</translate>
                    <select v-model="taskSolverType">
                        <option value="autor"><translate>für Studierende</translate></option>
                        <option value="group"><translate>für Gruppen</translate></option>
                    </select>
                </label>
            </fieldset>
            <fieldset v-show="taskSolverType === 'autor'" class="cw-manager-task-distributor-task-solvers">
                <legend><translate>Aufgabe Studierenden zuweisen</translate></legend>
                <courseware-companion-box
                    v-show="autor_members.length === 0"
                    :msgCompanion="$gettext('Es wurden keine Studierenden in dieser Veranstaltung gefunden.')"
                    mood="pointing"
                />
                <table v-show="autor_members.length > 0" class="default">
                    <thead>
                        <tr>
                            <th><input type="checkbox" v-model="bulkSelectAutors"/></th>
                            <th><translate>Name</translate></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in autor_members" :key="user.user_id">
                            <td><input type="checkbox" v-model="selectedAutors" :value="user.user_id" /></td>
                            <td>{{ user.formattedname }}</td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <fieldset v-show="taskSolverType === 'group'" class="cw-manager-task-distributor-task-solvers">
                <legend><translate>Aufgabe Gruppen zuweisen</translate></legend>
                <courseware-companion-box
                    v-show="groups.length === 0"
                    :msgCompanion="$gettext('Es wurden keine Gruppen in dieser Veranstaltung gefunden.')"
                    mood="pointing"
                />
                <table v-show="groups.length > 0" class="default">
                    <thead>
                        <tr>
                            <th><input type="checkbox" v-model="bulkSelectGroups"/></th>
                            <th><translate>Gruppenname</translate></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="group in groups" :key="group.id">
                            <td><input type="checkbox" v-model="selectedGroups" :value="group.id" /></td>
                            <td>{{ group.name }}</td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
            <footer>
                <button class="button" name="create_task" :disabled="!targetSelected" @click="createTask">
                    <translate>Aufgabe verteilen</translate>
                </button>
                <span 
                    v-if="!targetSelected"
                    class="tooltip tooltip-icon "
                    :data-tooltip="$gettext('Bitte wählen aus, an welcher Stelle die Aufgabe eingefügt werden soll.')"
                    tabindex="0"
                    title=""
                >
                </span>
            </footer>
        </form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

export default {
    name: 'courseware-manager-task-distributor',
    components: {
        CoursewareCompanionBox,
    },
    data() {
        return {
            ownCoursewareInstance: null,
            ownCoursewareElements: [],
            taskSolverType: 'autor',
            selectedElementId: '',
            selectedAutors: [],
            bulkSelectAutors: false,
            selectedGroups: [],
            bulkSelectGroups: false,
            taskTitle: '',
            submissionDate: '',
            solverMayAddBlocks: true,
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            userId: 'userId',
            structuralElementById: 'courseware-structural-elements/byId',
            relatedCourseMemberships: 'course-memberships/related',
            relatedCourseStatusGroups: 'status-groups/related',
            relatedUser: 'users/related',
            filingData: 'filingData',
        }),
        users() {
            const parent = { type: 'courses', id: this.context.id };
            const relationship = 'memberships';
            const memberships = this.relatedCourseMemberships({ parent, relationship });

            return (
                memberships?.map((membership) => {
                    const parent = { type: membership.type, id: membership.id };
                    const member = this.relatedUser({ parent, relationship: 'user' });

                    return {
                        user_id: member.id,
                        formattedname: member.attributes['formatted-name'],
                        username: member.attributes['username'],
                        perm: membership.attributes['permission'],
                    };
                }) ?? []
            );
        },
        groups() {
            const parent = { type: 'courses', id: this.context.id };
            const relationship = 'status-groups';
            const statusGroups = this.relatedCourseStatusGroups({ parent, relationship });

            return (
                statusGroups?.map((statusGroup) => {
                    return {
                        id: statusGroup.id,
                        name: statusGroup.attributes['name'],
                    };
                }) ?? []
            );
        },
        autor_members() {
            if (Object.keys(this.users).length === 0 && this.users.constructor === Object) {
                return [];
            }

            let members = this.users
                .filter(function (user) {
                    return user.perm === 'autor';
                })
                .map((obj) => ({ ...obj, active: false }));

            return members;
        },
        ownCoursewareRootId() {
            if (this.ownCoursewareInstance !== null) {
                return this.ownCoursewareInstance.relationships.root.data.id;
            } else {
                return '';
            }
        },
        ownCoursewareRoot() {
            if (this.ownCoursewareRootId !== '') {
                return this.structuralElementById({ id: this.ownCoursewareRootId });
            } else {
                return null;
            }
        },
        taskTemplates() {
            let templates = this.ownCoursewareElements.filter((elem) => {
                return elem.attributes.purpose === 'template';
            });

            return templates;
        },
        targetSelected() {
            return this.filingData.itemType === 'element';
        },
    },
    methods: {
        ...mapActions({
            loadCourseMemberships: 'course-memberships/loadRelated',
            loadCourseStatusGroups: 'status-groups/loadRelated',
            loadRemoteCoursewareStructure: 'loadRemoteCoursewareStructure',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            loadStructuralElement: 'loadStructuralElement',
            createTaskGroup: 'createTaskGroup',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
        }),
        async loadOwnCourseware() {
            this.ownCoursewareInstance = await this.loadRemoteCoursewareStructure({
                rangeId: this.userId,
                rangeType: 'users',
            });
            await this.loadStructuralElementById({ id: this.ownCoursewareRootId, options: { include: 'children' } });
            let children = this.ownCoursewareRoot.relationships.children.data;
            for (let i = 0; i < children.length; i++) {
                this.ownCoursewareElements.push(this.structuralElementById({ id: children[i].id }));
            }
        },
        async createTask() {
            if (!this.targetSelected) {
                return;
            }

            if (this.taskTitle === '') {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie einen Aufgabentitel aus.'),
                });

                return false;
            }
            if (this.selectedElementId.trim() === '') {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie eine Aufgabenvorlage aus.'),
                });

                return false;
            }
            if (this.submissionDate === '') {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie eine Abgabefrist aus.'),
                });

                return false;
            }
            if (!['autor', 'group'].includes(this.taskSolverType)) {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie aus, an wen die Aufgabe verteilt werden sollen.'),
                });

                return false;
            }
            if (this.taskSolverType === 'autor') {
                if (this.selectedAutors.length === 0) {
                    this.companionWarning({
                        info: this.$gettext('Bitte wählen Sie mindestens einen Studierenden aus.'),
                    });
                    return false;
                }
            }
            if (this.taskSolverType === 'group') {
                if (this.selectedGroups.length === 0) {
                    this.companionWarning({
                        info: this.$gettext('Bitte wählen Sie mindestens eine Gruppe aus.'),
                    });
                    return false;
                }
            }

            const taskGroup = {
                attributes: {
                    title: this.taskTitle,
                    'submission-date': new Date(this.submissionDate).toISOString(),
                    'solver-may-add-blocks': this.solverMayAddBlocks,
                },
                relationships: {
                    solvers: {
                        data: [],
                    },
                    target: {
                        data: {
                            id: this.filingData.parentItem.id,
                            type: 'courseware-structural-elements',
                        },
                    },
                    'task-template': {
                        data: {
                            id: this.selectedElementId,
                            type: 'courseware-structural-elements',
                        },
                    },
                },
            };

            let solvers;
            if (this.taskSolverType === 'autor') {
                solvers = this.selectedAutors.map((id) => ({ type: 'users', id }));
            }
            if (this.taskSolverType === 'group') {
                solvers = this.selectedGroups.map((id) => ({ type: 'status-groups', id }));
            }
            taskGroup.relationships.solvers.data = solvers;

            await this.createTaskGroup({ taskGroup });

            this.resetTask();

            this.companionSuccess({
                info: this.$gettext('Aufgabe wurde verteilt.'),
            });
        },
        resetTask() {
            this.taskTitle = '';
            this.taskSolverType = 'autor';
            this.selectedElementId = '';
            this.submissionDate = '';
            this.solverMayAddBlocks = true;
            this.bulkSelectAutors = false;
            this.selectedAutors = [];
            this.bulkSelectGroups = false;
            this.selectedGroups = [];
        }
    },
    mounted() {
        const parent = { type: 'courses', id: this.context.id };
        this.loadCourseMemberships({ parent, relationship: 'memberships', options: { include: 'user', 'page[offset]': 0, 'page[limit]': 10000, 'filter[permission]': 'autor' } });
        this.loadCourseStatusGroups({ parent, relationship: 'status-groups' });
        this.loadOwnCourseware();
    },
    watch: {
        bulkSelectAutors(newState) {
            if (newState) {
                this.selectedAutors = this.autor_members.map( autor => autor.user_id);
            } else {
                this.selectedAutors = [];
            }
        },
        bulkSelectGroups(newState) {
            if (newState) {
                this.selectedGroups = this.groups.map( group => group.id);
            } else {
                this.selectedGroups = [];
            }
        }
    }
};
</script>
