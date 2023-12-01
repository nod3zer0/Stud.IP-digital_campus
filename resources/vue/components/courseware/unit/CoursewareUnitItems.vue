<template>
    <div class="cw-unit-items">
        <h2 v-if="!inCourseContext && hasUnits">{{ $gettext('Persönliche Lernmaterialien') }}</h2>
        <template v-if="hasUnits">
            <ol v-if="(!userIsTeacher && inCourseContext) || units.length === 1" class="cw-tiles">
                <courseware-unit-item v-for="unit in units" :key="unit.id" :unit="unit" :handle="false"/>
            </ol>
            <template v-else>
                <span aria-live="assertive" class="assistive-text">{{ assistiveLive }}</span>
                <span id="operation" class="assistive-text">
                    {{ $gettext('Drücken Sie die Leertaste oder Entertaste, um neu anzuordnen.') }}
                </span>
                <draggable
                    tag="ol"
                    role="listbox"
                    v-model="unitList"
                    v-bind="dragOptions"
                    handle=".cw-tile-handle"
                    group="units"
                    @start="isDragging = true"
                    @end="dropUnit"
                    ref="sortables"
                    class="cw-tiles"
                >
                    <courseware-unit-item
                        v-for="unit in unitList"
                        :key="unit.id"
                        :unit="unit"
                        @unit-keydown="keyHandler($event, unit.id)"
                    />
                </draggable>
            </template>
        </template>
        <template v-if="!hasUnits && inCourseContext">
            <div v-if="userIsTeacher" class="cw-contents-overview-teaser">
                <div class="cw-contents-overview-teaser-content">
                    <header>{{ $gettext('Lernmaterialien') }}</header>
                    <p>
                        {{
                            $gettext(
                                'Mit Courseware können Sie interaktive, multimediale Lerninhalte erstellen und nutzen. ' +
                                    'Die Lerninhalte lassen sich hierarchisch unterteilen und können aus Texten, Videosequenzen, ' +
                                    'Aufgaben, Kommunikationselementen und einer Vielzahl weiterer Elemente bestehen. ' +
                                    'Fertige Lerninhalte können exportiert und in andere Kurse oder andere Installationen importiert werden. ' +
                                    'Courseware ist nicht nur für digitale Formate geeignet, sondern kann auch genutzt werden, ' +
                                    'um klassische Präsenzveranstaltungen mit Online-Anteilen zu ergänzen. Formate wie integriertes Lernen ' +
                                    '(Blended Learning) lassen sich mit Courseware ideal umsetzen. Kollaboratives Lernen kann dank Schreibrechtevergabe ' +
                                    'und dem Einsatz von Courseware in Studiengruppen realisiert werden.'
                            )
                        }}
                    </p>
                    <button class="button" @click="setShowUnitAddDialog(true)">
                        {{ $gettext('Neues Lernmaterial anlegen') }}
                    </button>
                </div>
            </div>
            <courseware-companion-box
                v-else
                :msgCompanion="$gettext('Es wurden leider noch keine Lernmaterialien angelegt.')"
                mood="sad"
            />
        </template>
        <div v-if="!hasUnits && !inCourseContext" class="cw-contents-overview-teaser">
            <div class="cw-contents-overview-teaser-content">
                <header>{{ $gettext('Ihre persönlichen Lernmaterialien') }}</header>
                <p>
                    {{
                        $gettext(
                            'Erstellen und verwalten Sie hier Ihre eigenen persönlichen Lernmaterialien in Form von ePorfolios, ' +
                                'Vorlagen für Veranstaltungen oder einfach nur persönliche Inhalte für das Studium. ' +
                                'Entwickeln Sie Ihre eigenen (Lehr-)Materialien für Studium oder die Lehre und teilen diese mit anderen Nutzenden.'
                        )
                    }}
                </p>
                <button class="button" @click="setShowUnitAddDialog(true)">
                    {{ $gettext('Neues Lernmaterial anlegen') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import CoursewareUnitItem from './CoursewareUnitItem.vue';
import draggable from 'vuedraggable';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-unit-items',
    components: {
        CoursewareCompanionBox,
        CoursewareUnitItem,
        draggable,
    },
    data() {
        return {
            isDragging: false,
            dragOptions: {
                animation: 0,
                disabled: false,
                ghostClass: 'unit-ghost',
            },
            unitList: [],
            assistiveLive: '',
            keyboardSelected: null,
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            coursewareUnits: 'courseware-units/all',
            coursewareUnitById: 'courseware-units/byId',
            structuralElementById: 'courseware-structural-elements/byId',
            userIsTeacher: 'userIsTeacher',
        }),
        units() {
            return (
                this.coursewareUnits
                    .filter((unit) => unit.relationships.range.data.id === this.context.id)
                    .sort((a, b) => a.attributes.position - b.attributes.position) ?? []
            );
        },
        hasUnits() {
            return this.units.length > 0;
        },
        inCourseContext() {
            return this.context.type === 'courses';
        },
    },
    methods: {
        ...mapActions({
            setShowUnitAddDialog: 'setShowUnitAddDialog',
            sortUnits: 'sortUnits',
        }),
        initCurrentData() {
            this.unitList = this.units;
        },
        dropUnit() {
            const positions = this.unitList.map((unit) => {
                return parseInt(unit.id);
            });
            this.sortUnits({ positions: positions });
        },
        getUnitTitle(unitId) {
            const unit = this.coursewareUnitById({ id: unitId });
            const element =
                this.structuralElementById({ id: unit.relationships['structural-element'].data.id }) ?? null;

            return element?.attributes?.title ?? '';
        },
        keyHandler(e, unitId) {
            switch (e.keyCode) {
                case 27: // esc
                    this.abortKeyboardSorting();
                    break;
                case 32: //space
                case 13: //enter
                    e.preventDefault();
                    if (this.keyboardSelected) {
                        this.storeKeyboardSorting();
                    } else {
                        this.keyboardSelected = { id: unitId, title: this.getUnitTitle(unitId) };
                        const index = this.unitList.findIndex((unit) => unit.id === unitId);
                        this.assistiveLive = this.$gettextInterpolate(
                            this.$gettext(
                                'Lernmaterial %{unitTitle} ausgewählt. Aktuelle Position in der Liste: %{pos} von %{listLength}. ' +
                                    'Drücken Sie die Aufwärts- und Abwärtspfeiltasten, um die Position zu ändern, die Leertaste oder ' +
                                    'Entertaste zum Ablegen, die Escape-Taste zum Abbrechen.'
                            ),
                            { unitTitle: this.keyboardSelected.title, pos: index + 1, listLength: this.unitList.length }
                        );
                    }
                    break;
            }
            if (this.keyboardSelected) {
                switch (e.keyCode) {
                    case 9: //tab
                        this.abortKeyboardSorting();
                        break;
                    case 37: // left
                    case 38: // up
                        e.preventDefault();
                        this.moveItemUp(unitId);
                        break;
                    case 39: // right
                    case 40: // down
                        e.preventDefault();
                        this.moveItemDown(unitId);
                        break;
                }
            }
        },
        abortKeyboardSorting() {
            this.assistiveLive = this.$gettextInterpolate(
                this.$gettext('Lernmaterial %{unitTitle}, Neuordnung abgebrochen.'),
                { unitTitle: this.keyboardSelected.title }
            );
            this.keyboardSelected = null;
            this.initCurrentData();
        },
        storeKeyboardSorting() {
            const index = this.unitList.findIndex((unit) => unit.id === this.keyboardSelected.id);
            this.assistiveLive = this.$gettextInterpolate(
                this.$gettext(
                    'Lernmaterial %{unitTitle}, abgelegt. Endgültige Position in der Liste: %{pos} von %{listLength}.'
                ),
                { unitTitle: this.keyboardSelected.title, pos: index + 1, listLength: this.unitList.length }
            );
            this.keyboardSelected = null;
            this.dropUnit();
        },
        moveItemUp(unitId) {
            const currentIndex = this.unitList.findIndex((unit) => unit.id === unitId);
            if (currentIndex !== 0) {
                const newPos = currentIndex - 1;
                this.unitList.splice(newPos, 0, this.unitList.splice(currentIndex, 1)[0]);
                this.focusHandle(unitId);
                this.assistiveLive = this.$gettextInterpolate(
                    this.$gettext(
                        'Lernmaterial %{unitTitle}. Aktuelle Position in der Liste: %{pos} von %{listLength}.'
                    ),
                    { unitTitle: this.keyboardSelected.title, pos: newPos + 1, listLength: this.unitList.length }
                );
            }
        },
        moveItemDown(unitId) {
            const currentIndex = this.unitList.findIndex((unit) => unit.id === unitId);
            if (this.unitList.length - 1 > currentIndex) {
                const newPos = currentIndex + 1;
                this.unitList.splice(newPos, 0, this.unitList.splice(currentIndex, 1)[0]);
                this.focusHandle(unitId);
                this.assistiveLive = this.$gettextInterpolate(
                    this.$gettext(
                        'Lernmaterial %{unitTitle}. Aktuelle Position in der Liste: %{pos} von %{listLength}.'
                    ),
                    { unitTitle: this.keyboardSelected.title, pos: newPos + 1, listLength: this.unitList.length }
                );
            }
        },
        focusHandle(unitId) {
            this.$nextTick(() => {
                document.getElementById('unit-handle-' + unitId).focus();
            });
        },
    },
    mounted() {
        this.initCurrentData();
    },
    watch: {
        units(newState) {
            this.initCurrentData();
        },
    },
};
</script>
