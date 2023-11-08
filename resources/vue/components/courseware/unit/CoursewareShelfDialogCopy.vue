<template>
    <studip-wizard-dialog
        :title="$gettext('Lernmaterial kopieren')"
        :confirmText="$gettext('Kopieren')"
        :closeText="$gettext('Abbrechen')"
        :lastRequiredSlotId="2"
        :requirements="requirements"
        :slots="wizardSlots"
        @close="close"
        @confirm="copy"
    >
        <template v-slot:source>
            <form class="default" @submit.prevent="">
                <fieldset class="radiobutton-set">
                    <template v-if="inCourseContext">
                        <input
                            id="cw-shelf-copy-source-self"
                            type="radio"
                            v-model="source"
                            value="self"
                            :aria-description="text.sourceSelf"
                        />
                        <label @click="source = 'self'" for="cw-shelf-copy-source-self">
                            <div class="icon"><studip-icon shape="seminar" size="32"/></div>
                            <div class="text">{{ text.sourceSelf }}</div>
                            <studip-icon shape="radiobutton-unchecked" size="24" class="unchecked" />
                            <studip-icon shape="check-circle" size="24" class="check" />
                        </label>
                    </template>
                    <input
                        id="cw-shelf-copy-source-courses"
                        type="radio"
                        v-model="source"
                        value="courses"
                        :aria-description="text.sourceCourses"
                    />
                    <label @click="source = 'courses'" for="cw-shelf-copy-source-courses">
                        <div class="icon"><studip-icon shape="seminar" size="32"/></div>
                        <div class="text">{{ text.sourceCourses }}</div>
                        <studip-icon shape="radiobutton-unchecked" size="24" class="unchecked" />
                        <studip-icon shape="check-circle" size="24" class="check" />
                    </label>
                    <input
                        id="cw-shelf-copy-source-users"
                        type="radio"
                        v-model="source"
                        value="users"
                        :aria-description="text.sourceUsers"
                    />
                    <label @click="source = 'users'" for="cw-shelf-copy-source-users">
                        <div class="icon"><studip-icon shape="content" size="32"/></div>
                        <div class="text">{{ text.sourceUsers }}</div>
                        <studip-icon shape="radiobutton-unchecked" size="24" class="unchecked" />
                        <studip-icon shape="check-circle" size="24" class="check" />
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
                            :clearable="false"
                            :reduce="option => option.id"
                            :getOptionLabel="option => option.attributes.title"
                            v-model="selectedRange"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"
                                    ><studip-icon shape="arr_1down" size="10"
                                /></span>
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
                            :id="'cw-shelf-copy-unit-' + unit.id"
                            type="radio"
                            v-model="selectedUnit"
                            :checked="unit.id === selectedUnitId"
                            :value="unit"
                            :key="'radio-' + unit.id"
                            :aria-description="unit.element.attributes.title"
                        />
                        <label @click="selectedUnit = unit" :key="'label-' + unit.id" :for="'cw-shelf-copy-unit-' + unit.id">
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
                    :msgCompanion="$gettext('Für die gewählte Quelle stehen keine Lernmaterialien zur Verfügung.')"
                />
            </form>
        </template>
        <template v-slot:edit>
            <form v-if="selectedUnit" class="default" @submit.prevent="">
                <label>
                    <span>{{$gettext('Titel')}}</span><span aria-hidden="true" class="wizard-required">*</span>
                    <input type="text" v-model="modifiedTitle" :placeholder="selectedUnitTitle" required />
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
                                ><studip-icon shape="arr_1down" size="10"
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
                    <span>{{$gettext('Beschreibung')}}</span><span aria-hidden="true" class="wizard-required">*</span>
                    <textarea v-model="modifiedDescription" :placeholder="selectedUnitDescription" required />
                </label>
            </form>
            <courseware-companion-box
                    v-else
                    mood="pointing"
                    :msgCompanion="$gettext('Bitte wählen Sie ein Lernmaterial aus.')"
                />
        </template>
    </studip-wizard-dialog>
</template>

<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import StudipSelect from '../../StudipSelect.vue';
import StudipWizardDialog from '../../StudipWizardDialog.vue';

import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'courseware-shelf-dialog-copy',
    mixins: [colorMixin],
    components: {
        CoursewareCompanionBox,
        StudipWizardDialog,
        StudipSelect,
    },
    data() {
        return {
            wizardSlots: [
                { id: 1, valid: false, name: 'source', title: this.$gettext('Quelle'), icon: 'source',
                  description: this.$gettext('Wählen Sie hier den Ort in Stud.IP aus, an dem sich das zu kopierende Lernmaterial befindet.') },
                { id: 2, valid: false, name: 'unit', title: this.$gettext('Lernmaterial'), icon: 'courseware',
                  description: this.$gettext('Wählen Sie hier das gewünschte Lernmaterial aus der Liste aus. Eine Auswahl wird durch einen grauen Hintergrund und einen Kontrollhaken angezeigt.') },
                { id: 3, valid: true, name: 'edit', title: this.$gettext('Anpassen'), icon: 'edit',
                  description: this.$gettext('Sie können hier die Daten des zu kopierenden Lernmaterials anpassen. Eine Anpassung ist optional, Sie können das Lernmaterial auch unverändert kopieren.') },
            ],
            source: '',
            loadingCourses: false,
            courses: [],
            semesterMap: [],
            selectedSemester: 'all',
            selectedRange: '',
            loadingUnits: false,
            selectedUnit: null,
            selectedUnitElement: null,
            modifiedTitle: '',
            modifiedColor: '',
            modifiedDescription: '',

            requirements: [],
            text: {
                source: this.$gettext('Quelle'),
                unit: this.$gettext('Lernmaterial'),
                sourceSelf: this.$gettext('Diese Veranstaltung'),
                sourceCourses: this.$gettext('Veranstaltung'),
                sourceUsers: this.$gettext('Arbeitsplatz'),

            }
        }
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            coursewareUnits: 'courseware-units/all',
            semesterById: 'semesters/byId',
            structuralElementById: 'courseware-structural-elements/byId',
            context: 'context'
        }),
        colors() {
            return this.mixinColors.filter(color => color.darkmode);
        },
        units() {
            let units = this.coursewareUnits.filter(unit => unit.relationships.range.data.id === this.selectedRange);
            units.forEach(unit => {
                unit.element = this.getUnitElement(unit);
            });

            if (this.inCourseContext) {
                units = units.filter(unit => unit.element.attributes.purpose !== 'template');
            }

            return units;
        },
        selectedUnitId() {
            return this.selectedUnit?.id;
        },
        inCourseContext() {
            return this.context.type === 'courses';
        },
        selectedUnitTitle() {
            return this.selectedUnitElement.attributes.title ?? '';
        },
        selectedUnitDescription() {
            return this.selectedUnitElement.attributes.payload.description ?? '';
        },
        filteredCourses() {
            const courses = this.courses.filter((course) => { return course.id !== this.context.id });
            if (this.selectedSemester === 'all') {
                return courses;
            } else {
                return courses.filter((course) => {
                    return course.relationships['start-semester'].data.id === this.selectedSemester;
                });
            }
        }
    },
    async mounted() {
        this.initWizardData();
    },
    methods: {
        ...mapActions({
            companionSuccess: 'companionSuccess',
            loadCourseUnits: 'loadCourseUnits',
            loadUsersCourses: 'loadUsersCourses',
            loadSemester: 'semesters/loadById',
            loadUserUnits: 'loadUserUnits',
            setShowUnitCopyDialog: 'setShowUnitCopyDialog',
            copyUnit: 'copyUnit',
        }),
        initWizardData() {
            this.source = this.inCourseContext ? 'self' : 'users';
            this.selectedRange = '';
            this.selectedUnit = null;
        },
        close() {
            this.setShowUnitCopyDialog(false);
            this.initWizardData();
        },
        getUnitElement(unit) {
            return this.structuralElementById({id: unit.relationships['structural-element'].data.id});
        },
        async copy() {
            if (this.selectedUnit) {
                const element = this.getUnitElement(this.selectedUnit);
                const modified = {
                        title: this.modifiedTitle !== '' ? this.modifiedTitle : this.selectedUnitTitle,
                        color: this.modifiedColor,
                        description: this.modifiedDescription !== '' ? this.modifiedDescription : this.selectedUnitDescription
                }
                await this.copyUnit({ unitId: this.selectedUnit.id, modified: modified });
                this.companionSuccess({ info: this.$gettext('Lernmaterial kopiert.') });
                this.close();
            }
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
                    view.semesterMap.sort((a, b) => new Date(b.attributes.start) - new Date(a.attributes.start));
                });
                return true;
            });
        },
        async updateCourseUnits(cid) {
            this.loadingUnits = true;
            await this.loadCourseUnits(cid);
            this.loadingUnits = false;
        },
        setElementData() {
            this.selectedUnitElement = this.getUnitElement(this.selectedUnit);
            this.modifiedTitle = this.selectedUnitElement.attributes.title;
            this.modifiedColor = this.selectedUnitElement.attributes.payload.color;
            this.modifiedDescription = this.selectedUnitElement.attributes.payload.description;
        },
        resetElementData() {
            this.modifiedTitle = '';
            this.modifiedColor = '';
            this.modifiedDescription = '';
        },
        validateSelection() {
            this.requirements = [];
            if (this.selectedRange === '') {
                this.requirements.push({slot: this.wizardSlots[0], text: this.text.source });
            }
            if (this.selectedUnit === null) {
                this.requirements.push({slot: this.wizardSlots[1], text: this.text.unit });
            }
        }
    },
    watch: {
        selectedUnit(newUnit) {
            this.validateSelection();
            const slot = this.wizardSlots[1];
            if (newUnit !== null) {
                slot.valid = true;
                this.setElementData();
            } else {
                slot.valid = false;
                this.resetElementData();
            }
        },
        selectedRange(newRid) {
            this.selectedUnit = null;
            this.validateSelection();
            const slot = this.wizardSlots[0];
            
            if (newRid !== '') {
                slot.valid = true;
                if (this.source === 'courses' || this.source === 'self') {
                    this.updateCourseUnits(newRid);
                }
                if (this.source === 'users') {
                    this.loadUserUnits(newRid);
                }
            } else {
                slot.valid = false;
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
