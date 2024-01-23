<template>
    <studip-dialog
        :title="$gettext('Teilnehmende hinzufügen')"
        :confirmText="$gettext('Hinzufügen')"
        confirmClass="accept"
        :confirmDisabled="!taskSolverType"
        :closeText="$gettext('Abbrechen')"
        closeClass="cancel"
        @close="onClose"
        @confirm="onConfirm"
        width="700"
    >
        <template #dialogContent>
            <form class="default">
                <label>
                    {{ $gettext('Verteilen an') }}
                    <select v-model="taskSolverType">
                        <option value="users">{{ $gettext('Studierende') }}</option>
                        <option value="status-groups">{{ $gettext('Gruppen') }}</option>
                    </select>
                </label>

                <template v-if="taskSolverType === 'users'">
                    <CoursewareCompanion
                        v-show="autor_members.length === 0"
                        :msgCompanion="$gettext('Es wurden keine Studierenden in dieser Veranstaltung gefunden.')"
                        mood="pointing"
                    />
                    <table v-show="autor_members.length > 0" class="default">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ $gettext('Name') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in autor_members" :key="user.user_id">
                                <td>
                                    <input
                                        type="checkbox"
                                        v-model="selectedAutors"
                                        :disabled="isSolver(user.user_id)"
                                        :value="user.user_id"
                                        :aria-label="
                                            $gettextInterpolate($gettext('%{userName} auswählen'), {
                                                userName: user.formattedname,
                                            })
                                        "
                                    />
                                </td>
                                <td>{{ user.formattedname }}</td>
                            </tr>
                        </tbody>
                    </table>
                </template>
                <template v-if="taskSolverType === 'status-groups'">
                    <CoursewareCompanion
                        v-show="groups.length === 0"
                        :msgCompanion="$gettext('Es wurden keine Gruppen in dieser Veranstaltung gefunden.')"
                        mood="pointing"
                    />
                    <table v-show="groups.length > 0" class="default">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ $gettext('Gruppenname') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="group in groups" :key="group.id">
                                <td>
                                    <input
                                        type="checkbox"
                                        v-model="selectedGroups"
                                        :disabled="isSolver(group.id)"
                                        :value="group.id"
                                        :aria-label="
                                            $gettextInterpolate($gettext('%{groupName} auswählen'), {
                                                groupName: group.name,
                                            })
                                        "
                                    />
                                </td>
                                <td>{{ group.name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </form>
        </template>
    </studip-dialog>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CoursewareCompanion from '../layouts/CoursewareCompanionBox.vue';

export default {
    props: ['taskGroup'],
    components: {
        CoursewareCompanion,
    },
    data: () => ({
        selectedAutors: [],
        selectedGroups: [],
        storing: false,
        taskSolverType: null,
    }),
    computed: {
        ...mapGetters({
            context: 'context',
            relatedCourseMemberships: 'course-memberships/related',
            relatedCourseStatusGroups: 'status-groups/related',
            relatedUser: 'users/related',
            tasksByCid: 'tasks/tasksByCid',
        }),
        autor_members() {
            return Object.keys(this.users).length === 0 && this.users.constructor === Object
                ? []
                : this.users.filter(({ perm }) => perm === 'autor').map((obj) => ({ ...obj, active: false }));
        },
        groups() {
            return (
                this.relatedCourseStatusGroups({
                    parent: { type: 'courses', id: this.context.id },
                    relationship: 'status-groups',
                })?.map(({ id, attributes: { name } }) => ({ id, name })) ?? []
            );
        },
        solversById() {
            return new Map(this.solvers.map(({ id, type }) => [id, { id, type }]));
        },
        solvers() {
            return this.tasks.map((task) => task.relationships.solver.data);
        },
        tasks() {
            return this.tasksByCid(this.context.id).filter(
                (task) => task.relationships['task-group'].data.id === this.taskGroup.id
            );
        },
        users() {
            const memberships = this.relatedCourseMemberships({
                parent: { type: 'courses', id: this.context.id },
                relationship: 'memberships',
            });

            return (
                memberships?.map(({ type, id, attributes: { permission } }) => {
                    const member = this.relatedUser({ parent: { type, id }, relationship: 'user' });

                    return {
                        user_id: member.id,
                        formattedname: member.attributes['formatted-name'],
                        username: member.attributes['username'],
                        perm: permission,
                    };
                }) ?? []
            );
        },
    },
    methods: {
        ...mapActions({
            addSolversToTaskGroup: 'tasks/addSolversToTaskGroup',
            loadCourseMemberships: 'course-memberships/loadRelated',
            loadCourseStatusGroups: 'status-groups/loadRelated',
            setShowDialog: 'tasks/setShowTaskGroupsAddSolversDialog',
        }),
        isSolver(id) {
            return !!this.solvers.find((solver) => solver.id === id);
        },
        onClose() {
            this.setShowDialog(false);
        },
        onConfirm() {
            if (!this.taskSolverType || this.storing) {
                return;
            }
            this.storing = true;

            const solvers = this[this.taskSolverType === 'users' ? 'selectedAutors' : 'selectedGroups'];
            const ids = solvers.filter((id) => !this.solversById.has(id));
            this.addSolversToTaskGroup({
                taskGroup: this.taskGroup,
                solvers: ids.map((id) => ({ id, type: this.taskSolverType })),
            })
                .then(() => {
                    this.$emit('newtask');
                    this.onClose();
                })
                .finally(() => (this.storing = false));
        },
        resetLocalVars() {
            this.selectedAutors = this.solvers.filter(({ type }) => type === 'users').map(({ id }) => id);
            this.selectedGroups = this.solvers.filter(({ type }) => type === 'status-groups').map(({ id }) => id);
            this.taskSolverType = this.selectedAutors.length
                ? 'users'
                : this.selectedGroups.length
                ? 'status-groups'
                : null;
        },
    },
    mounted() {
        this.resetLocalVars();

        const parent = { type: 'courses', id: this.context.id };
        this.loadCourseMemberships({
            parent,
            relationship: 'memberships',
            options: {
                include: 'user',
                'page[offset]': 0,
                'page[limit]': 10000,
                'filter[permission]': 'autor',
            },
        });
        this.loadCourseStatusGroups({ parent, relationship: 'status-groups' });
    },
    watch: {
        taskGroup() {
            this.resetLocalVars();
        },
    },
};
</script>
