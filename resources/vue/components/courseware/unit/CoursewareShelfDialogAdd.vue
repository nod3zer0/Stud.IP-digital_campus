<template>
    <studip-wizard-dialog
        :title="$gettext('Lernmaterial hinzufügen')"
        :confirmText="$gettext('Erstellen')"
        :closeText="$gettext('Abbrechen')"
        :slots="wizardSlots"
        :lastRequiredSlotId="1"
        :requirements="requirements"
        @close="setShowUnitAddDialog(false)"
        @confirm="createUnit"
    >
        <template v-slot:basic>
            <form class="default" @submit.prevent="">
                <label>
                    <span>{{ text.title }}</span><span aria-hidden="true" class="wizard-required">*</span>
                    <input type="text" v-model="addWizardData.title" required/>
                </label>
                <label>
                    <span>{{ text.description }}</span><span aria-hidden="true" class="wizard-required">*</span>
                    <textarea v-model="addWizardData.description" required/>
                </label>
            </form>
        </template>
        <template v-slot:layout>
            <form class="default" @submit.prevent="">
                <label>
                        {{ $gettext('Bild hochladen') }}
                        <br>
                        <input class="cw-file-input" ref="upload_image" type="file" accept="image/*" @change="checkUploadFile"/>
                        <CoursewareCompanionBox
                            v-if="uploadFileError"
                            :msgCompanion="uploadFileError"
                            mood="sad"
                            class="cw-companion-box-in-form"
                        />
                </label>
                <template v-if="selectedStockImage">
                    <StockImageSelectableImageCard :stock-image="selectedStockImage" />
                    <label>
                        <button class="button" type="button" @click="selectedStockImage = null">
                            {{ $gettext('Bild entfernen') }}
                        </button>
                    </label>
                </template>
                <label v-else>
                    {{ $gettext('oder') }}
                    <br>
                    <button class="button" type="button" @click="showStockImageSelector = true">
                        {{ $gettext('Aus dem Bilderpool auswählen') }}
                    </button>
                    <StockImageSelector v-if="showStockImageSelector" @close="showStockImageSelector = false" @select="onSelectStockImage" />
                </label>
                <label>
                    {{ $gettext('Farbe') }}
                    <studip-select
                        v-model="addWizardData.color"
                        :options="colors"
                        :reduce="(color) => color.class"
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
            </form>
        </template>
        <template v-slot:advanced>
            <form class="default" @submit.prevent="">
                <label>
                    {{ $gettext('Art des Lernmaterials') }}
                    <select v-model="addWizardData.purpose">
                        <option value="content">{{ $gettext('Inhalt') }}</option>
                        <option value="oer">{{ $gettext('OER-Material') }}</option>
                        <option value="portfolio">{{ $gettext('ePortfolio') }}</option>
                        <option value="draft">{{ $gettext('Entwurf') }}</option>
                        <option v-if="!inCourseContext" value="template">{{ $gettext('Aufgabenvorlage') }}</option>
                        <option value="other">{{ $gettext('Sonstiges') }}</option>
                    </select>
                </label>
                <template v-if="purposeIsOer">
                    <label>
                        {{ $gettext('Lizenztyp') }}
                        <select v-model="addWizardData.license_type">
                            <option v-for="license in licenses" :key="license.id" :value="license.id">
                                {{ license.name }}
                            </option>
                        </select>
                    </label>
                    <label>
                        {{ $gettext('Geschätzter zeitlicher Aufwand') }}
                        <input type="text" v-model="addWizardData.required_time" />
                    </label>
                    <label>
                        {{ $gettext('Niveau') }}<br />
                        {{ $gettext('von') }}
                        <select v-model="addWizardData.difficulty_start">
                            <option
                                v-for="difficulty_start in 12"
                                :key="difficulty_start"
                                :value="difficulty_start"
                            >
                                {{ difficulty_start }}
                            </option>
                        </select>
                        {{ $gettext('bis') }}
                        <select v-model="addWizardData.difficulty_end">
                            <option
                                v-for="difficulty_end in 12"
                                :key="difficulty_end"
                                :value="difficulty_end"
                            >
                                {{ difficulty_end }}
                            </option>
                        </select>
                    </label>
                </template>
            </form>
        </template>
    </studip-wizard-dialog>
</template>

<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import StockImageSelectableImageCard from '../../stock-images/SelectableImageCard.vue';
import StockImageSelector from '../../stock-images/SelectorDialog.vue';
import StudipSelect from '../../StudipSelect.vue';
import StudipWizardDialog from '../../StudipWizardDialog.vue';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-shelf-dialog-add',
    mixins: [colorMixin],
    components: {
        CoursewareCompanionBox,
        StockImageSelectableImageCard,
        StockImageSelector,
        StudipWizardDialog,
        StudipSelect,
    },
    data() {
        return {
            wizardSlots: [
                { id: 1, valid: false, name: 'basic', title: this.$gettext('Grundeinstellungen'), icon: 'courseware',
                  description: this.$gettext('Wählen Sie einen kurzen, prägnanten Titel und beschreiben Sie in einigen Worten den Inhalt des Lernmaterials. Eine Beschreibung erleichtert Lernenden die Auswahl des Lernmaterials.') },
                { id: 2, valid: true, name: 'layout', title: this.$gettext('Erscheinung'), icon: 'picture',
                  description: this.$gettext('Ein Vorschaubild motiviert Lernende das Lernmaterial zu erkunden. Die Kombination aus Bild und Farbe erleichtert das wiederfinden des Lernmaterials in der Übersicht.') },
                { id: 3, valid: true, name: 'advanced', title: this.$gettext('Zusatzangaben'), icon: 'info-list',
                  description: this.$gettext('Hier können Sie detaillierte Angaben zum Lernmaterial eintragen. Diese sind besonders interessant wenn das Lernmaterial als OER geteilt wird.') }
            ],
            text: {
                title: this.$gettext('Titel des Lernmaterials'),
                description: this.$gettext('Beschreibung')
            },
            addWizardData: {},
            uploadFileError: '',
            requirements: [],
            showStockImageSelector: false,
            selectedStockImage: null,
        }
    },
    computed: {
        ...mapGetters({
            licenses: 'licenses',
            context: 'context',
            lastCreateCoursewareUnit: 'courseware-units/lastCreated',
            structuralElementById:  'courseware-structural-elements/byId',
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        },
        colors() {
            return this.mixinColors.filter(color => color.darkmode);
        },
        purposeIsOer() {
            return this.addWizardData.purpose === 'oer';
        },
    },
    mounted() {
        this.initAddWizardData();
    },
    methods: {
        ...mapActions({
            companionError: 'companionError',
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
            createCoursewareUnit: 'courseware-units/create',
            setShowUnitAddDialog: 'setShowUnitAddDialog',
            setStockImageForStructuralElement: 'setStockImageForStructuralElement',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            uploadImageForStructuralElement: 'uploadImageForStructuralElement',
        }),
        initAddWizardData() {
            this.addWizardData = {
                title: '',
                description: '',
                purpose: 'content',
                color: 'studip-blue',
            }
        },
        validateSlots() {
            let valid = true;
            this.wizardSlots.forEach(slot => {
                if (!slot.valid) {
                    valid = false;
                }
            });

            return valid;
        },
        checkUploadFile() {
            const file = this.$refs?.upload_image?.files[0];
            if (file.size > 2097152) {
                this.uploadFileError = this.$gettext('Diese Datei ist zu groß. Bitte wählen Sie eine Datei aus, die kleiner als 2MB ist.');
            } else if (!file.type.includes('image')) {
                this.uploadFileError = this.$gettext('Diese Datei ist kein Bild. Bitte wählen Sie ein Bild aus.');
            } else {
                this.uploadFileError = '';
                this.selectedStockImage = null;
            }
        },
        async createUnit() {
            if (!this.validateSlots()) {
                this.companionError({
                    info: this.$gettext('Bitte füllen Sie alle notwendigen Angaben aus.'),
                });
                return false;
            }
            const file = this.$refs?.upload_image?.files[0];
            const unit = {
                attributes: {
                    title: this.addWizardData.title,
                    purpose: this.addWizardData.purpose,
                    payload: {
                        description: this.addWizardData.description,
                        color: this.addWizardData.color,
                        license_type: this.purposeIsOer ? this.addWizardData.license_type : '',
                        required_time: this.purposeIsOer ? this.addWizardData.required_time : '',
                        difficulty_start: this.purposeIsOer ? this.addWizardData.difficulty_start : '',
                        difficulty_end: this.purposeIsOer ? this.addWizardData.difficulty_end : ''
                    }
                },
                relationships: {
                    range: {
                        data: {
                            type: this.context.type,
                            id: this.context.id
                        }
                    }
                }
            };
            this.setShowUnitAddDialog(false);

            await this.createCoursewareUnit(unit, { root: true });
            this.companionSuccess({ info: this.$gettext('Neues Lernmaterial angelegt.') });
            const newElementId = this.lastCreateCoursewareUnit.relationships['structural-element'].data.id
            await this.loadStructuralElementById({ id: newElementId });
            let newStructuralElement = this.structuralElementById({id: newElementId});

            try {
                if (file) {
                    await this.uploadImageForStructuralElement({
                        structuralElement: newStructuralElement,
                        file,
                    });
                } else if (this.selectedStockImage) {
                    await this.setStockImageForStructuralElement({
                        structuralElement: newStructuralElement,
                        stockImage: this.selectedStockImage,
                    })
                }

                this.loadStructuralElementById({id: newStructuralElement.id, options: {include: 'children'}});
            } catch(error) {
                console.error(error);
                this.companionError({ info: this.$gettext('Das Bild für das neue Lernmaterial konnte nicht gespeichert werden.') });
            }
        },
        onSelectStockImage(stockImage) {
            if (this.$refs?.upload_image) {
                this.$refs.upload_image.value = null;
            }
            this.selectedStockImage = stockImage;
            this.showStockImageSelector = false;
        },
    },
    watch: {
        addWizardData: {
            handler(newData) {
                this.requirements = [];
                const slot = this.wizardSlots[0];
                if (newData.title !== '' && newData.description !== '') {
                    slot.valid = true;
                }
                if (newData.title === '' ) {
                    slot.valid = false;
                    this.requirements.push({slot: slot, text: this.text.title });
                }
                if (newData.description === '') {
                    slot.valid = false;
                    this.requirements.push({slot:  slot, text: this.text.description });
                }
            },
            deep: true
        }
    }
}
</script>
