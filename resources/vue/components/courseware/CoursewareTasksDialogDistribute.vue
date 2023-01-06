<template>
    <studip-wizard-dialog
        :title="$gettext('Aufgabe verteilen')"
        :confirmText="$gettext('Verteilen')"
        :closeText="$gettext('Abbrechen')"
        :lastRequiredSlotId="6"
        :requirements="requirements"
        :slots="wizardSlots"
        @close="setShowTasksDistributeDialog(false)"
        @confirm="distributeTask"
    >
        <template v-slot:sourceunit>
            <form class="default" @submit.prevent="">
                <fieldset v-if="sourceUnits.length !== 0" class="radiobutton-set">
                    <template v-for="unit in sourceUnits">
                        <input
                            :id="'cw-task-dist-source-unit' + unit.id"
                            type="radio"
                            v-model="selectedSourceUnit"
                            :checked="unit.id === selectedSourceUnitId"
                            :value="unit"
                            :key="'radio-' + unit.id"
                            :aria-description="unit.element.attributes.title"
                        />
                        <label @click="selectedSourceUnit = unit" :key="'label-' + unit.id" :for="'cw-task-dist-source-unit' + unit.id">
                            <div class="icon"><studip-icon shape="courseware" size="32"/></div>
                            <div class="text">{{ unit.element.attributes.title }}</div>
                            <studip-icon shape="radiobutton-unchecked" size="24" class="unchecked" />
                            <studip-icon shape="check-circle" size="24" class="check" />
                        </label>
                    </template>
                </fieldset>
                <courseware-companion-box
                    v-else
                    mood="sad"
                    :msgCompanion="$gettext('Es stehen keine Lernmaterialien zur Verfügung.')"
                />
            </form>
        </template>
        <template v-slot:task>
            <form v-if="selectedSourceUnit" class="default" @submit.prevent="">
                <fieldset class="radiobutton-set">
                    <input id="cw-task-dist-task" type="radio" :checked="selectedTaskIsTask" :aria-description="selectedTaskTitle"/>
                    <label for="cw-task-dist-task" @click="e => e.preventDefault()">
                        <div class="icon"><studip-icon shape="content2" size="32"/></div>
                        <div class="text">{{ selectedTaskTitle }}</div>
                        <studip-icon v-if="selectedTaskIsTask" shape="check-circle" size="24" class="check" />
                        <studip-icon v-else shape="decline-circle" size="24" class="unchecked" />
                    </label>
                </fieldset>
                <button
                    v-if="selectedTaskParent"
                    class="button"
                    @click="selectTask(selectedTaskParent.id)"
                >
                    {{  $gettext('zurück zur übergeordneten Seite') }}
                </button>
                <fieldset>
                    <legend>{{ $gettext('Unterseiten') }}</legend>
                    <ul class="cw-element-selector-list">
                        <li
                            v-for="child in taskChildren"
                            :key="child.id"
                        >
                            <button
                                class="cw-element-selector-item"
                                @click="selectTask(child.id)"
                            >
                                {{ child.attributes.title }}
                            </button>
                        </li>
                        <li v-if="taskChildren.length === 0">
                            {{ $gettext('Es wurden keine Unterseiten gefunden.') }}
                        </li>
                    </ul>
                </fieldset>
            </form>
            <courseware-companion-box
                    v-else
                    mood="pointing"
                    :msgCompanion="$gettext('Bitte wählen Sie ein Lernmaterial aus.')"
            />
        </template>
        <template v-slot:tasksettings>
            <form v-if="selectedTaskIsTask" class="default" @submit.prevent="">
                <label>
                    <span>{{ $gettext('Aufgabentitel') }}</span><span aria-hidden="true" class="wizard-required">*</span>
                    <input type="text" v-model="taskTitle" required/>
                </label>
                <label>
                    <span>{{ $gettext('Abgabefrist') }}</span><span aria-hidden="true" class="wizard-required">*</span>
                    <input type="date" v-model="submissionDate" />
                </label>
                <label>
                    {{ $gettext('Inhalte ergänzen') }}
                    <select class="size-s" v-model="solverMayAddBlocks">
                        <option value="true">{{ $gettext('ja') }}</option>
                        <option value="false">{{ $gettext('nein') }}</option>
                    </select>
                </label>
            </form>
            <courseware-companion-box
                v-else
                mood="pointing"
                :msgCompanion="$gettext('Bitte wählen Sie eine Aufgabenvorlage aus.')"
            />
        </template>
        <template v-slot:targetunit>
            <form v-if="selectedTaskIsTask" class="default" @submit.prevent="">
                <fieldset v-if="targetUnits.length !== 0" class="radiobutton-set">
                    <template v-for="unit in targetUnits">
                        <input
                            :id="'cw-task-dist-target-unit' + unit.id"
                            type="radio"
                            v-model="selectedTargetUnit"
                            :checked="unit.id === selectedTargetUnitId"
                            :value="unit"
                            :key="'radio-' + unit.id"
                            :aria-description="unit.element.attributes.title"
                        />
                        <label @click="selectedTargetUnit = unit" :key="'label-' + unit.id" :for="'cw-task-dist-target-unit' + unit.id">
                            <div class="icon"><studip-icon shape="courseware" size="32"/></div>
                            <div class="text">{{ unit.element.attributes.title }}</div>
                            <studip-icon shape="radiobutton-unchecked" size="24" class="unchecked" />
                            <studip-icon shape="check-circle" size="24" class="check" />
                        </label>
                    </template>
                </fieldset>
                <courseware-companion-box
                    v-else
                    mood="sad"
                    :msgCompanion="$gettext('Es stehen keine Lernmaterialien zur Verfügung.')"
                />
            </form>
            <courseware-companion-box
                v-else
                mood="sad"
                :msgCompanion="$gettext('Bitte wählen Sie eine Aufgabe aus.')"
            />
        </template>
        <template v-slot:targetelement>
            <form v-if="selectedTargetUnit && selectedTaskIsTask" class="default" @submit.prevent="">
                <fieldset class="radiobutton-set">
                    <input id="cw-task-dist-target-element" type="radio" checked  :aria-description="selectedTargetElementTitle"/>
                    <label for="cw-task-dist-target-element" @click="e => e.preventDefault()">
                        <div class="icon"><studip-icon shape="content2" size="32"/></div>
                        <div class="text">{{ selectedTargetElementTitle }}</div>
                        <studip-icon shape="check-circle" size="24" class="check" />
                    </label>
                </fieldset>
                <button
                    v-if="selectedTargetElementParent"
                    class="button"
                    @click="selectTargetElement(selectedTargetElementParent.id)"
                >
                    {{  $gettext('zurück zur übergeordneten Seite') }}
                </button>
                <fieldset>
                    <legend>{{ $gettext('Unterseiten') }}</legend>
                    <ul class="cw-element-selector-list">
                        <li
                            v-for="child in targetChildren"
                            :key="child.id"
                        >
                            <button
                                class="cw-element-selector-item"
                                @click="selectTargetElement(child.id)"
                            >
                                {{ child.attributes.title }}
                            </button>
                        </li>
                        <li v-if="targetChildren.length === 0">
                            {{ $gettext('Es wurden keine Unterseiten gefunden.') }}
                        </li>
                    </ul>
                </fieldset>
            </form>
            <courseware-companion-box
                v-if="!selectedTaskIsTask"
                mood="sad"
                :msgCompanion="$gettext('Bitte wählen Sie eine Aufgabe aus.')"
            />
            <courseware-companion-box
                v-if="!selectedTargetUnit"
                mood="sad"
                :msgCompanion="$gettext('Bitte wählen Sie ein Lernmaterial als Ziel aus.')"
            />
        </template>
        <template v-slot:solver>
            <form v-if="selectedTargetElement && selectedTaskIsTask" class="default" @submit.prevent="">
                <label>
                    {{ $gettext('Verteilen an') }}
                    <select v-model="taskSolverType">
                        <option value="autor">{{ $gettext('Studierende') }}</option>
                        <option value="group">{{ $gettext('Gruppen') }}</option>
                    </select>
                </label>
                <template v-if="taskSolverType === 'autor'" >
                    <courseware-companion-box
                        v-show="autor_members.length === 0"
                        :msgCompanion="$gettext('Es wurden keine Studierenden in dieser Veranstaltung gefunden.')"
                        mood="pointing"
                    />
                    <table v-show="autor_members.length > 0" class="default">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="bulkSelectAutors"/></th>
                                <th>{{ $gettext('Name') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in autor_members" :key="user.user_id">
                                <td><input type="checkbox" v-model="selectedAutors" :value="user.user_id" /></td>
                                <td>{{ user.formattedname }}</td>
                            </tr>
                        </tbody>
                    </table>
                </template>
                <template v-if="taskSolverType === 'group'">
                    <courseware-companion-box
                        v-show="groups.length === 0"
                        :msgCompanion="$gettext('Es wurden keine Gruppen in dieser Veranstaltung gefunden.')"
                        mood="pointing"
                    />
                    <table v-show="groups.length > 0" class="default">
                        <thead>
                            <tr>
                                <th><input type="checkbox" v-model="bulkSelectGroups"/></th>
                                <th>{{ $gettext('Gruppenname') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="group in groups" :key="group.id">
                                <td><input type="checkbox" v-model="selectedGroups" :value="group.id" /></td>
                                <td>{{ group.name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </form>
            <courseware-companion-box
                v-if="!selectedTaskIsTask"
                mood="sad"
                :msgCompanion="$gettext('Bitte wählen Sie eine Aufgabe aus.')"
            />
            <courseware-companion-box
                v-if="!selectedTargetElement"
                mood="sad"
                :msgCompanion="$gettext('Bitte wählen Sie eine Seite aus.')"
            />
        </template>
    </studip-wizard-dialog>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import StudipWizardDialog from './../StudipWizardDialog.vue';

import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'courseware-tasks-dialog-distribute',
    components: {
        CoursewareCompanionBox,
        StudipWizardDialog,
    },
    data() {
        return {
            wizardSlots: [
                { id: 1, valid: false, name: 'sourceunit', title: this.$gettext('Lernmaterial'), icon: 'courseware',
                  description: this.$gettext('Wählen Sie das Lernmaterial aus, in dem sich die Aufgabenvorlage befindet. Es sind nur Lernmaterialien aus Ihrem Arbeitsplatz aufgeführt.') },
                { id: 2, valid: false, name: 'task', title: this.$gettext('Aufgabenvorlage'), icon: 'category-task',
                  description: this.$gettext('Wählen Sie die zu verteilende Aufgabenvorlage aus. Vorausgewählt ist die oberste Seite des ausgewählten Lernmaterials. Unterseiten erreichen Sie über die Schaltflächen im Bereich "Unterseiten". Sie können über die "zurück zu" Schaltfläche das übergeordnete Element anwählen. Die ausgewählte Aufgabenvorlage ist mit einem Kontrollhaken markiert. Nur Seiten der Kategorie "Aufgabenvorlage" können verteilt werden.') },
                { id: 3, valid: false, name: 'tasksettings', title: this.$gettext('Aufgabeneinstellungen'), icon: 'settings',
                  description: this.$gettext('Wählen Sie hier die Einstellungen der Aufgabe. Es muss ein Aufgabentitel und eine Abgabenfrist gesetzt werden.') },
                { id: 4, valid: false, name: 'targetunit', title: this.$gettext('Ziel-Lernmaterial'), icon: 'courseware',
                  description: this.$gettext('Wählen Sie hier das Lernmaterial aus, in das die Aufgabe verteilt werden soll. Zum Bearbeiten der Aufgabe müssen Lernende Zugriff auf das Lernmaterial haben. Prüfen Sie gegebenenfalls die Leserechte und die Sichtbarkeit.') },
                { id: 5, valid: false, name: 'targetelement', title: this.$gettext('Zielseite'), icon: 'content2',
                  description: this.$gettext('Wählen Sie hier die Seite aus unterhalb der die Aufgabe verteilt werden soll. Zum bearbeiten der Aufgabe müssen Lernende Zugriff auf die Seite haben. Prüfen Sie ggf. die Leserechte und die Sichtbarkeit.') },
                { id: 6, valid: false, name: 'solver', title: this.$gettext('Aufgabe zuweisen'), icon: 'group3',
                  description: this.$gettext('Wählen Sie hier aus, an wen Sie die Aufgaben verteilen möchten. Aufgaben können entweder an Gruppen oder einzelne Teilnehmende verteilt werden. Über die Checkbox im Titel der Tabelle können Sie alles aus- bzw. abwählen.') },
            ],
            selectedSourceUnit: null,
            taskTitle: '',
            submissionDate: '',
            solverMayAddBlocks: true,
            selectedTask: null,
            selectedTargetUnit: null,
            selectedTargetElement: null,
            taskSolverType: 'autor',
            selectedAutors: [],
            bulkSelectAutors: false,
            selectedGroups: [],
            bulkSelectGroups: false,
            requirements: [],
        }
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            coursewareUnits: 'courseware-units/all',
            structuralElementById: 'courseware-structural-elements/byId',
            structuralElements: 'courseware-structural-elements/all',
            context: 'context',
            currentElement: 'currentElement',
            relatedCourseMemberships: 'course-memberships/related',
            relatedCourseStatusGroups: 'status-groups/related',
            relatedUser: 'users/related',
        }),
        selectedSourceUnitId() {
            return this.selectedSourceUnit?.id;
        },
        selectedSourceUnitRootId() {
            return this.selectedSourceUnit?.relationships?.['structural-element']?.data?.id;
        },
        sourceUnits() {
            let units = this.coursewareUnits.filter(unit => unit.relationships.range.data.id === this.userId);
            units.forEach(unit => {
                unit.element = this.getUnitElement(unit);
            });

            return units;
        },
        selectedTargetUnitId() {
            return this.selectedTargetUnit?.id;
        },
        selectedTargetUnitRootId() {
            return this.selectedTargetUnit?.relationships?.['structural-element']?.data?.id;
        },
        targetUnits() {
            let units = this.coursewareUnits.filter(unit => unit.relationships.range.data.id === this.context.id);
            units.forEach(unit => {
                unit.element = this.getUnitElement(unit);
            });

            return units;
        },
        selectedTaskIsTask() {
            return this.selectedTask?.attributes?.purpose === 'template';
        },
        selectedTaskTitle() {
            return this.selectedTask?.attributes?.title;
        },
        selectedTaskParent() {
            let parentData = this.selectedTask?.relationships?.parent?.data;
            if (parentData){
                return this.structuralElementById({id: parentData.id});
            }

            return null;
        },
        selectedTaskParentTitle() {
            if (this.selectedTaskParent) {
                return this.selectedTaskParent.attributes.title;
            }

            return '';
        },
        taskChildren() {
            let children = [];
            if (this.selectedTask) {
                children = this.structuralElements.filter(
                    element => element.relationships.parent?.data?.id === this.selectedTask.id
                );
            }

            return children;
        },
        selectedTargetElementTitle() {
            return this.selectedTargetElement?.attributes?.title;
        },
        selectedTargetElementParent() {
            let parentData = this.selectedTargetElement?.relationships?.parent?.data;
            if (parentData){
                return this.structuralElementById({id: parentData.id});
            }

            return null;
        },
        selectedTargetElementParentTitle() {
            if (this.selectedTargetElementParent) {
                return this.selectedTargetElementParent.attributes.title;
            }

            return '';
        },
        targetChildren() {
            let children = [];
            if (this.selectedTargetElement) {
                children = this.structuralElements.filter(
                    element => element.relationships.parent?.data?.id === this.selectedTargetElement.id
                );
            }

            return children;
        },
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
    },
    mounted() {
        this.initWizardData();
        const parent = { type: 'courses', id: this.context.id };
        this.loadCourseMemberships({ parent, relationship: 'memberships', options: { include: 'user', 'page[offset]': 0, 'page[limit]': 10000, 'filter[permission]': 'autor' } });
        this.loadCourseStatusGroups({ parent, relationship: 'status-groups' });
    },
    methods: {
        ...mapActions({
            setShowTasksDistributeDialog: 'setShowTasksDistributeDialog',
            loadCourseUnits: 'loadCourseUnits',
            loadUserUnits: 'loadUserUnits',
            loadUsersCourses: 'loadUsersCourses',
            loadStructuralElement: 'courseware-structural-elements/loadById',
            copyStructuralElement: 'copyStructuralElement',
            companionError: 'companionError',
            companionSuccess: 'companionSuccess',
            loadCourseMemberships: 'course-memberships/loadRelated',
            loadCourseStatusGroups: 'status-groups/loadRelated',
            createTaskGroup: 'createTaskGroup',
        }),
        async initWizardData() {
            this.loadUserUnits(this.userId);
            this.loadCourseUnits(this.context.id);
            this.validate();
        },
        getUnitElement(unit) {
            return this.structuralElementById({id: unit.relationships['structural-element'].data.id});
        },
        selectTask(id) {
            this.selectedTask =  this.structuralElementById({id: id});
            this.loadStructuralElement({id: id, options: {include: 'children'}});
        },
        selectTargetElement(id) {
            this.selectedTargetElement = this.structuralElementById({id: id});
            this.loadStructuralElement({id: id, options: {include: 'children'}});
        },
        async distributeTask() {
            this.setShowTasksDistributeDialog(false);
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
                            id: this.selectedTargetElement.id,
                            type: 'courseware-structural-elements',
                        },
                    },
                    'task-template': {
                        data: {
                            id: this.selectedTask.id,
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
            this.companionSuccess({ info: this.$gettext('Aufgaben wurden verteilt.') });

        },
        validateSolvers() {
            if (
                (this.selectedAutors.length > 0 && this.taskSolverType === 'autor') ||
                (this.selectedGroups.length > 0 && this.taskSolverType === 'group')
            ) {
                this.wizardSlots[5].valid = true;
            } else {
                this.wizardSlots[5].valid = false;
            }

            return this.wizardSlots[5].valid;
        },
        validateTaskSettings() {
            if (this.taskTitle !== '' && this.submissionDate !== '') {
                this.wizardSlots[2].valid = true;
            } else {
                this.wizardSlots[2].valid = false;
            }

            return this.wizardSlots[2].valid;
        },
        validate() {
            this.requirements = [];
            if (this.selectedSourceUnit === null) {
                this.requirements.push({ slot: this.wizardSlots[0], text: this.$gettext('Lernmaterial') });
            }
            if (!this.selectedTaskIsTask) {
                this.requirements.push({ slot: this.wizardSlots[1], text: this.$gettext('Aufgabenvorlage') });
            }
            if (!this.validateTaskSettings()) {
                this.requirements.push({ slot: this.wizardSlots[2], text: this.$gettext('Aufgabeneinstellungen') });
            }
            if (this.selectedTargetUnit === null) {
                this.requirements.push({ slot: this.wizardSlots[3], text: this.$gettext(' Ziel-Lernmaterial') });
            }
            if (this.selectedTargetElement === null) {
                this.requirements.push({ slot: this.wizardSlots[4], text: this.$gettext(' Zielseite') });
            }
            if (!this.validateSolvers()) {
                this.requirements.push({ slot: this.wizardSlots[5], text: this.$gettext('Aufgabe zuweisen') });
            }
        }
    },
    watch: {
        async selectedSourceUnit(newUnit) {
            this.validate();
            if (newUnit !== null) {
                this.wizardSlots[0].valid = true;
                await this.loadStructuralElement({id: this.selectedSourceUnitRootId, options: {include: 'children'}});
                this.selectedTask = this.structuralElementById({id: this.selectedSourceUnitRootId});
            } else {
                this.wizardSlots[0].valid = false;
            }
        },
        selectedTask(newTask) {
            this.validate();
            if (newTask !== null && this.selectedTaskIsTask) {
                this.wizardSlots[1].valid = true;
            } else {
                this.wizardSlots[1].valid = false;
            }
        },
        async selectedTargetUnit(newUnit) {
            this.validate();
            if (newUnit !== null) {
                this.wizardSlots[3].valid = true;
                await this.loadStructuralElement({id: this.selectedTargetUnitRootId, options: {include: 'children'}});
                this.selectedTargetElement = this.structuralElementById({id: this.selectedTargetUnitRootId});
            } else {
                this.wizardSlots[3].valid = false;
            }
        },
        selectedTargetElement(newElement) {
            this.validate();
            if (newElement !== null) {
                this.wizardSlots[4].valid = true;
            } else {
                this.wizardSlots[4].valid = false;
            }
        },

        taskTitle() {
            this.validate();
        },
        submissionDate() {
            this.validate();
        },

        selectedAutors() {
            this.validate();
        },
        selectedGroups() {
            this.validate();
        },
        taskSolverType() {
            this.validate();
        },
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
}
</script>
