<template>
    <studip-wizard-dialog
        :title="$gettext('Seiten verknüpfen')"
        :confirmText="$gettext('Verknüpfen')"
        :closeText="$gettext('Abbrechen')"
        :lastRequiredSlotId="2"
        :requirements="requirements"
        :slots="wizardSlots"
        @close="showElementLinkDialog(false)"
        @confirm="linkElement"
    >
        <template v-slot:unit>
            <form v-if="!loadingUnits" class="default" @submit.prevent="">
                <fieldset v-if="hasUnits" class="radiobutton-set">
                    <template v-for="unit in units">
                        <input
                            :id="'cw-element-link-unit-' + unit.id"
                            type="radio"
                            :checked="unit.id === selectedUnitId"
                            :value="unit.id"
                            :key="'radio-' + unit.id"
                            :aria-description="unit.element.attributes.title"
                        />
                        <label @click="selectedUnit = unit" :key="'label-' + unit.id" :for="'cw-element-link-unit-' + unit.id">
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
                    :msgCompanion="$gettext('Es konnte leider kein Lernmaterial gefunden werden.')"
                />
            </form>
            <studip-progress-indicator 
                v-else
                :description="$gettext('Lade Lernmaterialien…')"
            />
        </template>
        <template v-slot:element>
            <form class="default" @submit.prevent="">
                <template v-if="selectedUnit">
                    <fieldset class="radiobutton-set">
                        <input id="cw-element-link-element"  type="radio" checked :aria-description="selectedElementTitle"/>
                        <label for="cw-element-link-element" @click="e => e.preventDefault()">
                            <div class="icon"><studip-icon shape="content2" size="32"/></div>
                            <div class="text">{{ selectedElementTitle }}</div>
                            <studip-icon shape="check-circle" size="24" class="check" />
                        </label>
                    </fieldset>
                    <button
                        v-if="selectedElementParent"
                        class="button"
                        @click="selectElement(selectedElementParent.id)"
                    >
                        {{ $gettextInterpolate(
                            $gettext('zurück zu %{ parentTitle }'),
                            { parentTitle: selectedElementParentTitle }
                        ) }}
                    </button>
                    <fieldset>
                        <legend>{{ $gettext('Unterseiten') }}</legend>
                        <ul class="cw-element-selector-list">
                            <li
                                v-for="child in children"
                                :key="child.id"
                            >
                                <button
                                    class="cw-element-selector-item"
                                    @click="selectElement(child.id)"
                                >
                                    {{ child.attributes.title }}
                                </button>
                                
                            </li>
                            <li v-if="children.length === 0">
                                {{ $gettext('Es wurden keine Unterseiten gefunden.') }}
                            </li>
                        </ul>
                    </fieldset>
                </template>
                <courseware-companion-box
                    v-if="!selectedUnit"
                    mood="pointing"
                    :msgCompanion="$gettext('Bitte wählen Sie zuerst das Lernmaterial aus.')"
                />
            </form>
        </template>
    </studip-wizard-dialog>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import StudipWizardDialog from './../StudipWizardDialog.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';

import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'courseware-structural-element-dialog-link',
    components: {
        CoursewareCompanionBox,
        StudipWizardDialog,
        StudipProgressIndicator
    },
    data() {
        return {
            wizardSlots: [
                {id: 1, valid: false, name: 'unit', title: this.$gettext('Lernmaterial'), icon: 'courseware', 
                description: this.$gettext('Wählen Sie das Lernmaterial aus, in dem sich der zu verknüpfende Lerninhalt befindet.')},
                {id: 2, valid: false, name: 'element', title: this.$gettext('Seite'), icon: 'content2', 
                description: this.$gettext('Wählen Sie die zu verknüpfende Seite aus. Vorausgewählt ist die oberste Seite des ausgewählten Lernmaterials. Unterseiten erreichen Sie über die Schaltflächen im Bereich "Unterseiten". Sie können über die "zurück zu" Schaltfläche das übergeordnete Element anwählen. Die ausgewählte Seite ist mit einem Kontrollhaken markiert.')},            ],
            loadingUnits: false,
            selectedUnit: null,
            selectedElement: null,
            requirements: [],
            text: {

            }
        }
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            coursewareUnits: 'courseware-units/all',
            structuralElementById: 'courseware-structural-elements/byId',
            context: 'context',
            childrenById: 'courseware-structure/children',
            currentElement: 'currentElement'
        }),
        units() {
            let units = this.coursewareUnits.filter(unit => unit.relationships.range.data.id === this.userId);
            units.forEach(unit => {
                unit.element = this.getUnitElement(unit);
            });

            return units;
        },
        hasUnits() {
            return this.units.length !== 0;
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
    },
    mounted() {
        this.initWizardData();
        this.updateUserUnits();
        },
    methods: {
        ...mapActions({
            showElementLinkDialog: 'showElementLinkDialog',
            loadUserUnits: 'loadUserUnits',
            loadUsersCourses: 'loadUsersCourses',
            loadStructuralElement: 'courseware-structural-elements/loadById',
            linkStructuralElement: 'linkStructuralElement',
            companionError: 'companionError',
            companionSuccess: 'companionSuccess',
        }),
        initWizardData() {
            this.selectedRange = '';
            this.selectedUnit = null;
            this.validateSelection();
        },
        async updateUserUnits() {
            this.loadingUnits = true;
            await this.loadUserUnits(this.userId);
            this.loadingUnits = false;
        },
        getUnitElement(unit) {
            return this.structuralElementById({id: unit.relationships['structural-element'].data.id});
        },
        linkElement() {
            let view = this;
            this.linkStructuralElement({
                parentId: this.currentElement,
                        elementId: this.selectedElement.id,
                })
                .then( () => {
                    view.companionSuccess({
                        info: view.$gettextInterpolate(
                            view.$gettext('Die Seite %{ pageTitle } wurde erfolgreich verknüpft.'),
                            { pageTitle: view.selectedElementTitle }
                        )
                    });
                })
                .catch( () => {
                    view.companionError({
                        info: view.$gettextInterpolate(
                            view.$gettext('Die Seite %{ pageTitle } konnte nicht verknüpft werden.'),
                            { pageTitle: view.selectedElementTitle }
                        )
                    });
                })
                .finally(() => {
                    view.showElementLinkDialog(false);
                });
        },
        selectElement(id) {
            this.selectedElement = this.structuralElementById({id: id});
            this.loadStructuralElement({id: id, options: {include: 'children'}});
        },
        validateSelection() {
            this.requirements = [];
            if (this.selectedUnit === null) {
                this.requirements.push({slot: this.wizardSlots[0], text: this.$gettext('Lernmaterial') });
            }
        }
    },
    watch: {
        selectedElement(newElement) {
            this.validateSelection();
            if (newElement !== null) {
                this.wizardSlots[1].valid = true;
            } else {
                this.wizardSlots[1].valid = false;
            }
        },
        async selectedUnit(newUnit) {
            this.validateSelection();
            if (newUnit !== null) {
                this.wizardSlots[0].valid = true;
                await this.loadStructuralElement({id: this.selectedUnitRootId, options: {include: 'children'}});
                this.selectedElement = this.structuralElementById({id: this.selectedUnitRootId});
            } else {
                this.wizardSlots[0].valid = false;
            }
        },
    }
}
</script>