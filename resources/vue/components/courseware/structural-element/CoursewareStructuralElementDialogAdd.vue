<template>
    <studip-wizard-dialog
        :title="$gettext('Seite hinzufügen')"
        :confirmText="$gettext('Erstellen')"
        :closeText="$gettext('Abbrechen')"
        :slots="wizardSlots"
        :lastRequiredSlotId="1"
        :requirements="requirements"
        @close="closeAddDialog"
        @confirm="createElement"
    >
        <template v-slot:basic>
            <form class="default" @submit.prevent="">
                <label>
                    {{ $gettext('Position der neuen Seite') }}
                    <select v-model="pageParent">
                        <option v-if="!isRoot && canEditParent" value="sibling">
                            {{ $gettext('Neben der aktuellen Seite') }}
                        </option>
                        <option value="descendant">{{ $gettext('Unterhalb der aktuellen Seite') }}</option>
                    </select>
                </label>
                <label>
                    <span>{{ text.title }}</span>
                    <span aria-hidden="true" class="wizard-required">*</span>
                    <input type="text" v-model="title" required />
                </label>
                <label>
                    <span>{{ $gettext('Beschreibung') }}</span>
                    <textarea v-model="description" required />
                </label>
            </form>
        </template>
        <template v-slot:template>
            <form v-if="hasTemplates" class="default" @submit.prevent="">
                <label>
                    {{ $gettext('Art der Vorlage') }}
                    <select v-model="templatePurpose">
                        <option value="content">{{ $gettext('Inhalt') }}</option>
                        <option value="oer">{{ $gettext('OER-Material') }}</option>
                        <option value="portfolio">{{ $gettext('ePortfolio') }}</option>
                        <option value="draft">{{ $gettext('Entwurf') }}</option>
                        <option v-if="!inCourseContext" value="template">{{ $gettext('Aufgabenvorlage') }}</option>
                        <option value="other">{{ $gettext('Sonstiges') }}</option>
                    </select>
                </label>
                <label>
                    <span>{{ $gettext('Vorlage') }}</span>
                    <select v-model="selectedTemplate">
                        <option :value="null">{{ $gettext('ohne Vorlage') }}</option>
                        <option v-for="template in selectableTemplates" :key="template.id" :value="template">
                            {{ template.attributes.name }}
                        </option>
                    </select>
                </label>
            </form>
            <courseware-companion-box
                v-else
                :msgCompanion="$gettext('Es wurden keine Vorlagen gefunden.')"
                mood="pointing"
                class="cw-companion-box-in-form"
            />
        </template>
        <template v-slot:layout>
            <form class="default" @submit.prevent="">
                <label>
                    {{ $gettext('Bild') }}
                    <input
                        class="cw-file-input"
                        ref="upload_image"
                        type="file"
                        accept="image/*"
                        @change="checkUploadFile"
                    />
                    <courseware-companion-box
                        v-if="uploadFileError"
                        :msgCompanion="uploadFileError"
                        mood="sad"
                        class="cw-companion-box-in-form"
                    />
                </label>
                <label>
                    {{ $gettext('Farbe') }}
                    <studip-select v-model="color" :options="colors" :reduce="(color) => color.class" label="class">
                        <template #open-indicator="selectAttributes">
                            <span v-bind="selectAttributes"><studip-icon shape="arr_1down" :size="10" /></span>
                        </template>
                        <template #no-options>
                            {{ $gettext('Es steht keine Auswahl zur Verfügung.') }}
                        </template>
                        <template #selected-option="{ name, hex }">
                            <span class="vs__option-color" :style="{ 'background-color': hex }"></span>
                            <span>{{ name }}</span>
                        </template>
                        <template #option="{ name, hex }">
                            <span class="vs__option-color" :style="{ 'background-color': hex }"></span>
                            <span>{{ name }}</span>
                        </template>
                    </studip-select>
                </label>
            </form>
        </template>
        <template v-slot:advanced>
            <form class="default" @submit.prevent="">
                <label>
                    {{ $gettext('Art des Lernmaterials') }}
                    <select v-model="purpose">
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
                        <select v-model="license_type">
                            <option v-for="license in licenses" :key="license.id" :value="license.id">
                                {{ license.name }}
                            </option>
                        </select>
                    </label>
                    <label>
                        {{ $gettext('Geschätzter zeitlicher Aufwand') }}
                        <input type="text" v-model="required_time" />
                    </label>
                    <label>
                        {{ $gettext('Niveau') }}<br />
                        {{ $gettext('von') }}
                        <select v-model="difficulty_start">
                            <option v-for="difficulty_start in 12" :key="difficulty_start" :value="difficulty_start">
                                {{ difficulty_start }}
                            </option>
                        </select>
                        {{ $gettext('bis') }}
                        <select v-model="difficulty_end">
                            <option v-for="difficulty_end in 12" :key="difficulty_end" :value="difficulty_end">
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
import StudipSelect from '../../StudipSelect.vue';
import StudipWizardDialog from '../../StudipWizardDialog.vue';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import wizardMixin from '@/vue/mixins/courseware/wizard.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-dialog-add',
    mixins: [colorMixin, wizardMixin],
    components: {
        CoursewareCompanionBox,
        StudipWizardDialog,
        StudipSelect,
    },
    props: {
        structuralElement: Object,
        isRoot: Boolean,
        canEditParent: Boolean,
    },
    data() {
        return {
            wizardSlots: [
                {
                    id: 1,
                    valid: false,
                    name: 'basic',
                    title: this.$gettext('Grundeinstellungen'),
                    icon: 'courseware',
                    description: this.$gettext(
                        'Wählen Sie einen kurzen, prägnanten Titel und beschreiben Sie in einigen Worten den Inhalt der Seite.'
                    ),
                },
                {
                    id: 2,
                    valid: true,
                    name: 'template',
                    title: this.$gettext('Vorlage'),
                    icon: 'content2',
                    description: this.$gettext('Vorlagen enthalten Abschnitte und Blöcke, die bereits für bestimmte Zwecke angeordent sind. Beim anlegen der Seite, wird diese mit Abschnitten und Blöcken befüllt.'),
                },
                {
                    id: 3,
                    valid: true,
                    name: 'layout',
                    title: this.$gettext('Erscheinung'),
                    icon: 'picture',
                    description: this.$gettext(
                        'Ein Vorschaubild motiviert Lernende die Seite zu erkunden. Die Kombination aus Bild und Farbe erleichtert das wiederfinden der Seite in einem Inhaltsverzeichnisblock.'
                    ),
                },
                {
                    id: 4,
                    valid: true,
                    name: 'advanced',
                    title: this.$gettext('Zusatzangaben'),
                    icon: 'info-list',
                    description: this.$gettext(
                        'Hier können Sie detaillierte Angaben zur Seite eintragen. Diese sind besonders interessant wenn die Seite als OER geteilt wird.'
                    ),
                },
            ],
            text: {
                title: this.$gettext('Titel der neuen Seite'),
            },
            uploadFileError: '',
            requirements: [],

            pageParent: '',
            title: '',
            description: '',
            purpose: '',
            color: '',
            license_type: '',
            required_time: '',
            difficulty_start: '',
            difficulty_end: '',
            templatePurpose: '',
            selectedTemplate: null,
        };
    },
    computed: {
        ...mapGetters({
            licenses: 'licenses',
            context: 'context',
            lastCreatedStructuralElement: 'courseware-structural-elements/lastCreated',
            structuralElementById: 'courseware-structural-elements/byId',
            templates: 'courseware-templates/all',
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        },
        colors() {
            return this.mixinColors.filter((color) => color.darkmode);
        },
        selectableTemplates() {
            return this.templates.filter((template) => {
                return template.attributes.purpose === this.templatePurpose;
            });
        },
        hasTemplates() {
            return this.templates.length > 0;
        },
        purposeIsOer() {
            return this.purpose === 'oer';
        },
    },
    methods: {
        ...mapActions({
            createStructuralElementWithTemplate: 'createStructuralElementWithTemplate',
            updateStructuralElement: 'updateStructuralElement',
            companionError: 'companionError',
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
            showElementAddDialog: 'showElementAddDialog',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            uploadImageForStructuralElement: 'uploadImageForStructuralElement',
        }),
        initWizardData() {
            this.pageParent = 'descendant';
            this.title = '';
            this.description = '';
            this.purpose = 'content';
            this.color = 'studip-blue';
            this.license_type = '';
            this.required_time = '';
            this.difficulty_start = '';
            this.difficulty_end = '';
            this.templatePurpose = 'content';
            this.selectedTemplate = null;
            this.requirements.push({ slot: this.wizardSlots[0], text: this.text.title });
        },
        closeAddDialog() {
            this.showElementAddDialog(false);
            this.initWizardData();
        },
        checkUploadFile() {
            this.uploadFileError = this.checkUploadImageFile(this.$refs?.upload_image?.files[0]);
        },
        async createElement() {
            let parent_id = this.structuralElement.id; // new page is descandant as default

            this.errorEmptyChapterName = this.title.trim();
            if (this.errorEmptyChapterName === '') {
                return;
            }
            if (this.pageParent === 'sibling') {
                parent_id = this.structuralElement.relationships.parent.data.id;
            }
            this.showElementAddDialog(false);

            const file = this.$refs?.upload_image?.files[0];
            const element = {
                attributes: {
                    title: this.title,
                    purpose: this.purpose,
                    payload: {
                        description: this.description,
                        color: this.color,
                        license_type: this.purposeIsOer ? this.license_type : '',
                        required_time: this.purposeIsOer ? this.required_time : '',
                        difficulty_start: this.purposeIsOer ? this.difficulty_start : '',
                        difficulty_end: this.purposeIsOer ? this.difficulty_end : '',
                    },
                },
                templateId: this.selectedTemplate ? this.selectedTemplate.id : null,
                parentId: parent_id,
                currentId: this.structuralElement.id,
            };

            try {
                await this.createStructuralElementWithTemplate(element);
            } catch (e) {
                let errorMessage = this.$gettext(
                    'Es ist ein Fehler aufgetreten. Die Seite konnte nicht erstellt werden.'
                );
                if (e.status === 403) {
                    errorMessage = this.$gettext(
                        'Die Seite konnte nicht erstellt werden. Sie haben nicht die notwendigen Schreibrechte.'
                    );
                }

                this.companionError({ info: errorMessage });
                return;
            }

            const newCreated = this.lastCreatedStructuralElement;
            await this.loadStructuralElementById({ id: newCreated.id });
            const newElement = this.structuralElementById({ id: newCreated.id });
            this.companionSuccess({
                info: this.$gettextInterpolate(this.$gettext('Die Seite %{ pageTitle } wurde erfolgreich angelegt.'), {
                    pageTitle: newElement.attributes.title,
                }),
            });

            if (file && this.uploadFileError === '') {
                try {
                    await this.uploadImageForStructuralElement({ structuralElement: newElement, file });
                } catch (error) {
                    console.error(error);
                    this.companionError({
                        info: this.$gettext('Das Bild für das neue Lernmaterial konnte nicht gespeichert werden.'),
                    });
                }
                this.loadStructuralElementById({ id: newElement.id, options: { include: 'children' } });
            }
            this.initWizardData();
            this.$router.push(newElement.id);
        },
    },
    mounted() {
        this.initWizardData();
        if (!this.hasTemplates) {
                this.wizardSlots.splice(1,1);
                this.wizardSlots[1]['id'] = 2;
                this.wizardSlots[2]['id'] = 3;
        }
    },
    watch: {
        title(newTitle) {
            this.requirements = [];
            const slot = this.wizardSlots[0];
            if (newTitle === '') {
                slot.valid = false;
                this.requirements.push({ slot: slot, text: this.text.title });
            } else {
                slot.valid = true;
            }
        },
        templatePurpose(newPurpose) {
            this.selectedTemplate = null;
        },
    },
};
</script>
