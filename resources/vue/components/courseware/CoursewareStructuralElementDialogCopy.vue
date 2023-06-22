<template>
    <studip-wizard-dialog
        :title="$gettext('Seiten kopieren')"
        :confirmText="$gettext('Kopieren')"
        :closeText="$gettext('Abbrechen')"
        :lastRequiredSlotId="3"
        :requirements="requirements"
        :slots="wizardSlots"
        @close="showElementCopyDialog(false)"
        @confirm="copyElement"
    >
        <template v-slot:source>
            <form class="default" @submit.prevent="">
                <fieldset class="radiobutton-set">
                    <input
                        id="cw-element-copy-source-self"
                        v-if="inCourseContext"
                        type="radio"
                        v-model="source"
                        value="self"
                        :aria-description="text.sourceSelf"
                    />
                    <label v-if="inCourseContext" @click="source = 'self'" for="cw-element-copy-source-self">
                        <div class="icon"><studip-icon shape="seminar" :size="32"/></div>
                        <div class="text">{{ text.sourceSelf }}</div>
                        <studip-icon shape="radiobutton-unchecked" :size="24" class="unchecked" />
                        <studip-icon shape="check-circle" :size="24" class="check" />
                    </label>
                    <input
                        id="cw-element-copy-source-courses"
                        type="radio"
                        v-model="source"
                        value="courses"
                        :aria-description="text.sourceCourses"
                    />
                    <label @click="source = 'courses'" for="cw-element-copy-source-courses">
                        <div class="icon"><studip-icon shape="seminar" :size="32"/></div>
                        <div class="text">{{ text.sourceCourses }}</div>
                        <studip-icon shape="radiobutton-unchecked" :size="24" class="unchecked" />
                        <studip-icon shape="check-circle" :size="24" class="check" />
                    </label>
                    <input
                        id="cw-element-copy-source-users"
                        type="radio"
                        v-model="source"
                        value="users"
                        :aria-description="text.sourceUsers"
                    />
                    <label @click="source = 'users'" for="cw-element-copy-source-users">
                        <div class="icon"><studip-icon shape="content" :size="32"/></div>
                        <div class="text">{{ text.sourceUsers }}</div>
                        <studip-icon shape="radiobutton-unchecked" :size="24" class="unchecked" />
                        <studip-icon shape="check-circle" :size="24" class="check" />
                    </label>
                </fieldset>
                <template v-if="source === 'courses'">
                    <label>
                        <span>{{ $gettext('Semester') }}</span><span aria-hidden="true"></span>
                        <select v-model="selectedSemester">
                            <option value="all">{{ $gettext('Alle Semester') }}</option>
                            <option v-for="semester in semesterMap" :key="semester.id" :value="semester.id">
                                {{ semester.attributes.title }}
                            </option>
                        </select>
                    </label>
                    <label>
                        <span>{{ $gettext('Veranstaltung') }}</span><span aria-hidden="true" class="wizard-required">*</span>
                        <studip-select
                            v-if="filteredCourses.length !== 0 && !loadingCourses"
                            :options="filteredCourses"
                            label="title"
                            :clearable="false"
                            :reduce="option => option.id"
                            v-model="selectedRange"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"
                                    ><studip-icon shape="arr_1down" :size="10"
                                /></span>
                            </template>
                            <template #no-options="{}">
                                {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                            </template>
                            <template #selected-option="{ attributes }">
                                <span>{{ attributes.title }}</span>
                            </template>
                            <template #option="{ attributes }">
                                <span>{{ attributes.title }}</span>
                            </template>
                        </studip-select>
                        <p v-if="loadingCourses">
                            {{$gettext('Lade Veranstaltungen…')}}
                        </p>
                        <p v-if="filteredCourses.length === 0 && !loadingCourses">
                            {{$gettext('Es wurden keine geeigneten Veranstaltungen gefunden.')}}
                        </p>
                    </label>
                </template>

            </form>
        </template>
        <template v-slot:unit>
            <form class="default" @submit.prevent="">
                <fieldset v-if="units.length !== 0" class="radiobutton-set">
                    <template v-for="unit in units">
                        <input
                            :id="'cw-element-copy-unit-' + unit.id"
                            type="radio"
                            v-model="selectedUnit"
                            :checked="unit.id === selectedUnitId"
                            :value="unit"
                            :key="'radio-' + unit.id"
                            :aria-description="unit.element.attributes.title"
                        />
                        <label :key="'label-' + unit.id" :for="'cw-element-copy-unit-' + unit.id">
                            <div class="icon"><studip-icon shape="courseware" :size="32"/></div>
                            <div class="text">{{ unit.element.attributes.title }}</div>
                            <studip-icon shape="radiobutton-unchecked" :size="24" class="unchecked" />
                            <studip-icon shape="check-circle" :size="24" class="check" />
                        </label>
                    </template>
                </fieldset>
                <courseware-companion-box
                    v-else
                    mood="sad"
                    :msgCompanion="$gettext('Für die gewählte Quelle stehen kein Lernmaterialien zur Verfügung.')"
                />
            </form>
        </template>
        <template v-slot:element>
            <form v-if="selectedUnit" class="default" @submit.prevent="">
                <courseware-structural-element-selector
                    v-model="selectedElement"
                    :rootId="selectedUnitRootId"
                    :validateAncestors="true"
                    :targetId="currentElement"
                />
            </form>
            <courseware-companion-box
                v-else
                mood="pointing"
                :msgCompanion="$gettext('Bitte wählen Sie ein Lernmaterial aus.')"
            />
        </template>
        <template v-slot:edit>
            <form v-if="selectedUnit" class="default" @submit.prevent="">
                <label>
                    {{$gettext('Titel')}}
                    <input type="text" v-model="modifiedTitle" required />
                </label>
                <label>
                    {{$gettext('Farbe')}}
                    <studip-select
                        v-model="modifiedColor"
                        :options="colors"
                        :reduce="(color) => color.class"
                        :clearable="false"
                        label="class"
                    >
                        <template #open-indicator="selectAttributes">
                            <span v-bind="selectAttributes"
                                ><studip-icon shape="arr_1down" :size="10"
                            /></span>
                        </template>
                        <template #no-options>
                            {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                        </template>
                        <template #selected-option="{ name, hex }">
                            <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                            ><span>{{ name }}</span>
                        </template>
                        <template #option="{ name, hex }">
                            <span class="vs__option-color" :style="{ 'background-color': hex }"></span
                            ><span>{{ name }}</span>
                        </template>
                    </studip-select>
                </label>
                <label>
                    {{$gettext('Beschreibung')}}
                    <textarea v-model="modifiedDescription" required />
                </label>
            </form>
            <courseware-companion-box
                    v-else
                    mood="pointing"
                    :msgCompanion="$gettext('Bitte wählen Sie eine Seite aus.')"
            />
        </template>
    </studip-wizard-dialog>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareStructuralElementSelector from './CoursewareStructuralElementSelector.vue';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import StudipSelect from './../StudipSelect.vue';
import StudipWizardDialog from './../StudipWizardDialog.vue';

import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'courseware-structural-element-dialog-copy',
    mixins: [colorMixin],
    components: {
        CoursewareCompanionBox,
        CoursewareStructuralElementSelector,
        StudipWizardDialog,
        StudipSelect,
    },
    data() {
        return {
            wizardSlots: [
                { id: 1, valid: false, name: 'source', title: this.$gettext('Quelle'), icon: 'source',
                  description: this.$gettext('Wählen Sie hier den Ort in Stud.IP aus, an dem sich der zu kopierende Lerninhalt befindet.') },
                { id: 2, valid: false, name: 'unit', title: this.$gettext('Lernmaterial'), icon: 'courseware',
                  description: this.$gettext('Wählen Sie das Lernmaterial aus, in dem sich der zu kopierende Lerninhalt befindet.') },
                { id: 3, valid: false, name: 'element', title: this.$gettext('Seite'), icon: 'content2',
                  description: this.$gettext('Wählen Sie die zu kopierende Seite aus. Um Unterseiten anzuzeigen, klicken Sie auf den Seitennamen. Mit einem weiteren Klick werden die Unterseiten wieder zugeklappt.') },
                { id: 4, valid: true, name: 'edit', title: this.$gettext('Anpassen'), icon: 'edit',
                  description: this.$gettext('Sie können hier die Daten der zu kopierenden Seite anpassen. Eine Anpassung ist optional, Sie können die Seite auch unverändert kopieren.') },
            ],
            source: '',
            loadingCourses: false,
            courses: [],
            semesterMap: [],
            selectedSemester: 'all',
            selectedRange: '',
            loadingUnits: false,
            selectedUnit: null,
            selectedElement: null,
            modifiedTitle: '',
            modifiedColor: '',
            modifiedDescription: '',
            requirements: [],
            text: {
                sourceSelf: this.$gettext('Diese Veranstaltung'),
                sourceCourses: this.$gettext('Veranstaltung'),
                sourceUsers: this.$gettext('Arbeitsplatz'),
                source: this.$gettext('Quelle'),
                unit: this.$gettext('Lernmaterial'),
                element: this.$gettext('Seite'),
            },
        }
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            coursewareUnits: 'courseware-units/all',
            semesterById: 'semesters/byId',
            structuralElementById: 'courseware-structural-elements/byId',
            context: 'context',
            childrenById: 'courseware-structure/children',
            currentElement: 'currentElement'
        }),
        colors() {
            return this.mixinColors.filter(color => color.darkmode);
        },
        inCourseContext() {
            return this.context.type === 'courses';
        },
        units() {
            let units = this.coursewareUnits.filter(unit => unit.relationships.range.data.id === this.selectedRange);
            units.forEach(unit => {
                unit.element = this.getUnitElement(unit);
            });

            return units;
        },
        selectedUnitId() {
            return this.selectedUnit?.id;
        }, 
        selectedUnitRootId() {
            return this.selectedUnit?.relationships?.['structural-element']?.data?.id;
        }, 
        selectedElementTitle() {
            return this.selectedElement?.attributes?.title;
        },
        selectedElementParent() {
            let parentData = this.selectedElement?.relationships?.parent?.data;
            if (parentData){
                return this.structuralElementById({id: parentData.id});
            }

            return null;
        },
        selectedElementParentTitle() {
            if (this.selectedElementParent) {
                return this.selectedElementParent.attributes.title;
            }

            return '';
        },
        children() {
            if (!this.selectedElement) {
                return [];
            }

            return this.childrenById(this.selectedElement.id)
                .map((id) => this.structuralElementById({ id }))
                .filter(Boolean);
        },
        filteredCourses() {
            const courses = this.courses.filter((course) => { return course.id !== this.context.id});
            if (this.selectedSemester === 'all') {
                return courses;
            } else {
                return courses.filter((course) => {
                    return course.relationships['start-semester'].data.id === this.selectedSemester;
                });
            }
        }
    },
    mounted() {
        this.initWizardData();
    },
    methods: {
        ...mapActions({
            showElementCopyDialog: 'showElementCopyDialog',
            loadCourseUnits: 'loadCourseUnits',
            loadUserUnits: 'loadUserUnits',
            loadUsersCourses: 'loadUsersCourses',
            loadSemester: 'semesters/loadById',
            loadStructuralElement: 'courseware-structural-elements/loadById',
            copyStructuralElement: 'copyStructuralElement',
            companionError: 'companionError',
            companionSuccess: 'companionSuccess',
        }),
        initWizardData() {
            this.source = this.inCourseContext ? 'self' : 'users';
            this.selectedRange = '';
            this.selectedUnit = null;
        },
        getUnitElement(unit) {
            return this.structuralElementById({id: unit.relationships['structural-element'].data.id});
        },
        async updateCourses() {
            this.loadingCourses = true;
            this.courses = await this.loadUsersCourses({ userId: this.userId, withCourseware: true });
            this.loadSemesterMap();
            this.loadingCourses = false;
        },
        loadSemesterMap() {
            let view = this;
            let semesters = [];
            this.courses.every(course => {
                let semId = course.relationships['start-semester'].data.id;
                if(!semesters.includes(semId)) {
                    semesters.push(semId);
                }
                return true;
            });
            semesters.every(semester => {
                view.loadSemester({id: semester}).then( () => {
                    view.semesterMap.push(view.semesterById({id: semester}));
                    view.semesterMap.sort((a, b) => a.attributes.start < b.attributes.start);
                });
                return true;
            });
        },
        async updateCourseUnits(cid) {
            this.loadingUnits = true;
            await this.loadCourseUnits(cid);
            this.loadingUnits = false;
        },
        async updateUserUnits() {
            this.loadingUnits = true;
            await this.loadUserUnits(this.userId);
            this.loadingUnits = false;
        },
        selectElement(id) {
            this.selectedElement = this.structuralElementById({id: id});
            this.loadStructuralElement({id: id, options: {include: 'children'}});
        },
        setElementData() {
            this.modifiedTitle = this.selectedElement.attributes.title;
            this.modifiedColor = this.selectedElement.attributes.payload.color;
            this.modifiedDescription = this.selectedElement.attributes.payload.description;
        },
        resetElementData() {
            this.modifiedTitle = '';
            this.modifiedColor = '';
            this.modifiedDescription = '';
        },
        copyElement() {
            let view = this;
            this.copyStructuralElement({
                    parentId: this.currentElement,
                    elementId: this.selectedElement.id,
                    migrate: false,
                    modifications: {
                        title: view.modifiedTitle,
                        color: view.modifiedColor,
                        description: view.modifiedDescription
                    }
            })
            .then( () => {
                view.companionSuccess({
                    info: view.$gettextInterpolate(
                        view.$gettext('Die Seite %{ pageTitle } wurde erfolgreich kopiert.'),
                        {pageTitle: view.selectedElementTitle}
                    )
                });
            })
            .catch(error => {
                view.companionError({
                    info: view.$gettextInterpolate(
                        view.$gettext('Die Seite %{ pageTitle } konnte nicht kopiert werden.'),
                        {pageTitle: view.selectedElementTitle}
                    )
                });
            })
            .finally(() => {
                view.showElementCopyDialog(false);
            });
        },
        validateSelection() {
            this.requirements = [];
            if (this.selectedRange === '') {
                this.requirements.push({slot: this.wizardSlots[0], text: this.text.source });
            }
            if (this.selectedUnit === null) {
                this.requirements.push({slot: this.wizardSlots[1], text: this.text.unit });
            }
            if (this.selectedUnit === null) {
                this.requirements.push({slot: this.wizardSlots[2], text: this.text.element });
            }
        }
    },
    watch: {
        selectedElement(newElement) {
            this.validateSelection();
            if (newElement !== null) {
                this.wizardSlots[2].valid = true;
                this.setElementData();
            } else {
                this.resetElementData();
                this.wizardSlots[2].valid = false;
            }
            
        },
        async selectedUnit(newUnit) {
            this.validateSelection();
            if (newUnit !== null) {
                this.wizardSlots[1].valid = true;
                await this.loadStructuralElement({id: this.selectedUnitRootId, options: {include: 'children'}});
                this.selectedElement = null;
            } else {
                this.wizardSlots[1].valid = false;
            }
            
        },
        selectedRange(newRid) {
            this.validateSelection();
            this.selectedUnit = null;
            if (newRid !== '') {
                this.wizardSlots[0].valid = true;
                if (this.source === 'courses' || this.source === 'self') {
                    this.updateCourseUnits(newRid);
                }
                if (this.source === 'users') {
                    this.loadUserUnits(newRid);
                }
            } else {
                this.wizardSlots[0].valid = false;
            }
        },
        source(newSource) {
            switch (newSource) {
                case 'self':
                    this.selectedRange = this.context.id;
                    break;
                case 'courses':
                    this.selectedRange = '';
                    this.updateCourses();
                    break;
                case 'users':
                    this.selectedRange = this.userId;
                    break;
            }
        },
        selectedSemester(newSemester) {
            this.selectedRange = '';
        }
    }
}
</script>
