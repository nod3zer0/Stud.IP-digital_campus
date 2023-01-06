<template>
    <div class="cw-shelf-dialog-import-wrapper">
        <studip-wizard-dialog
            v-if="!importRunning"
            :title="$gettext('Lernmaterial importieren')"
            :confirmText="$gettext('Importieren')"
            :closeText="$gettext('Abbrechen')"
            :slots="wizardSlots"
            :lastRequiredSlotId="1"
            :requirements="requirements"
            @close="setShowUnitImportDialog(false)"
            @confirm="importCoursewareArchiv"
        >
            <template v-slot:file>
                <form class="default" @submit.prevent="">
                    <label>
                        <span>{{ text.import }}</span><span aria-hidden="true" class="wizard-required">*</span>
                        <input v-show="importZipFile === null" ref="fileInput" class="cw-file-input" type="file" accept=".zip" @change="setImport" />
                        <p v-show="importZipFile !== null" class="cw-file-input-change">
                            <button class="button" @click="$refs.fileInput.click()">{{ $gettext('Datei ändern')}}</button><span>{{ importZipFile?.name }}</span>
                        </p>
                    </label>
                    <fieldset v-show="archiveErrors.length > 0">
                        <legend>{{$gettext('Fehler im Import-Archiv')}}</legend>
                        <ul>
                            <li v-for="(error, index) in archiveErrors" :key="index"> {{error}} </li>
                        </ul>
                    </fieldset>
                </form>
            </template>
            <template v-slot:edit>
                <form v-if="hasValidFile" class="default" @submit.prevent="">
                    <label>
                        {{ text.title }}
                        <input type="text" v-model="modifiedData.title" :placeholder="loadedTitle" required />
                    </label>
                    <label>
                        {{ text.color }}
                        <studip-select
                            v-model="modifiedData.color"
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
                                {{$gettext('Es steht keine Auswahl zur Verfügung.')}}
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
                        {{ text.description }}
                        <textarea v-model="modifiedData.description" :placeholder="loadedDescription" required />
                    </label>
                </form>
                <courseware-companion-box 
                    v-else
                    :msgCompanion="$gettext('Bitte wählen Sie ein Import-Archiv aus.')"
                    mood="unsure"
                />
            </template>
        </studip-wizard-dialog>
        <studip-dialog
            v-if="importRunning"
            :title="$gettext('Lernmaterial importieren')"
            :closeText="$gettext('Schließen')"
            height="420"
            @close="setShowUnitImportDialog(false)"
        >
            <template v-slot:dialogContent>
                <div role="status" aria-live="polite">
                    <courseware-companion-box 
                        v-show="importDone && importErrors.length === 0"
                        :msgCompanion="$gettext('Import erfolgreich!')"
                        mood="special"
                    />
                    <courseware-companion-box
                        v-show="importDone && importErrors.length > 0"
                        :msgCompanion="$gettext('Import abgeschlossen. Es sind Fehler aufgetreten!')"
                        mood="unsure"
                    />
                    <courseware-companion-box
                        v-show="!importDone"
                        :msgCompanion="$gettext('Import läuft. Bitte schließen Sie den Dialog nicht bis der Import abgeschlossen wurde.')"
                        mood="pointing"
                    />
                </div>
                <form v-if="!importDone" class="default" @submit.prevent="">
                    <fieldset>
                        <div v-if="!fileImportDone" class="cw-import-zip">
                            <header>{{$gettext('Importiere Dateien')}}:</header>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" role="progressbar" :style="{width: importFilesProgress + '%'}" :aria-valuenow="importFilesProgress" aria-valuemin="0" aria-valuemax="100">{{ importFilesProgress }}%</div>
                            </div>
                            {{ importFilesState }}
                        </div>
                        <div v-if="fileImportDone" class="cw-import-zip">
                            <header>{{$gettext('Importiere Elemente')}}:</header>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" role="progressbar" :style="{width: importStructuresProgress + '%'}" :aria-valuenow="importStructuresProgress" aria-valuemin="0" aria-valuemax="100">{{ importStructuresProgress }}%</div>
                            </div>
                            {{ importStructuresState }}
                        </div>
                    </fieldset>
                    <fieldset v-show="importErrors.length > 0">
                        <legend>{{$gettext('Fehlermeldungen')}}</legend>
                        <ul>
                            <li v-for="(error, index) in importErrors" :key="index"> {{error}} </li>
                        </ul>
                    </fieldset>
                </form>
            </template>
        </studip-dialog>
    </div>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareImport from '@/vue/mixins/courseware/import.js';
import colorMixin from '@/vue/mixins/courseware/colors.js';
import StudipWizardDialog from './../StudipWizardDialog.vue';

import { mapActions, mapGetters } from 'vuex'
import JSZip from 'jszip';

export default {
    name: 'courseware-shelf-dialog-import',
    components: {
        StudipWizardDialog,
        CoursewareCompanionBox
    },
    mixins: [CoursewareImport, colorMixin],
    data() {
        return {
            wizardSlots: [
                { id: 1, valid: false, name: 'file', title: this.$gettext('Import-Archiv'), icon: 'file-archive',
                  description: this.$gettext('Wählen Sie hier eine Courseware-Export-Archiv-Datei von Ihrer Festplatte aus. Bei Courseware-Export-Archiven handelt es sich um Zip-Dateien. Diese sollten mindestens die Dateien files.json und courseware.json enthalten.') },
                { id: 2, valid: true, name: 'edit', title: this.$gettext('Anpassen'), icon: 'edit', description: this.$gettext('Sie können hier die Daten des zu importierenden Lernmaterials anpassen. Eine Anpassung ist optional, Sie können das Archiv auch unverändert importieren.') },
            ],
            modifiedData: {
                title: '',
                color: 'studip-blue',
                description: ''
            },
            importArchivFile: null,
            importRunning: false,
            importZipFile: null,
            zip: null,

            loadedZipData: null,
            archiveErrors: [],

            requirements: [],
            text: {
                import: this.$gettext('Importdatei'),
                title: this.$gettext('Titel'),
                color: this.$gettext('Farbe'),
                description: this.$gettext('Beschreibung'),
            }
        }
    },
    computed: {
        ...mapGetters({
            context: 'context',
            importFilesState: 'importFilesState',
            importFilesProgress: 'importFilesProgress',
            importStructuresState: 'importStructuresState',
            importStructuresProgress: 'importStructuresProgress',
            importErrors: 'importErrors',
            lastCreateCoursewareUnit: 'courseware-units/lastCreated',
            
        }),
        colors() {
            return this.mixinColors.filter(color => color.darkmode);
        },
        fileImportDone() {
            return this.importFilesProgress === 100;
        },
        importDone() {
            return this.importFilesProgress === 100 && this.importStructuresProgress === 100;
        },
        hasValidFile() {
            return this.archiveErrors.length === 0 && this.loadedZipData !== null;
        },
        loadedTitle() {
            return this.loadedZipData.courseware.attributes.title ?? '';
        },
        loadedDescription() {
            return this.loadedZipData.courseware.attributes.payload.description ?? '';
        }
    },
    methods: {
        ...mapActions({
            setShowUnitImportDialog: 'setShowUnitImportDialog',
            createCoursewareUnit: 'courseware-units/create',
            setImportFilesProgress: 'setImportFilesProgress',
            setImportStructuresProgress: 'setImportStructuresProgress',
            setImportErrors: 'setImportErrors',
            loadStructuralElementById: 'courseware-structural-elements/loadById',
            companionSuccess: 'companionSuccess',
        }),
        setImport(event) {
            this.importZipFile = event.target.files[0];
            this.loadZipData(); 
        },

        async loadZipData() {
            const slot = this.wizardSlots[0];
            const text = this.text.import;
            this.archiveErrors = [];
            this.loadedZipData = null;
            this.modifiedData.title = '';
            this.modifiedData.color = 'studip-blue';
            this.modifiedData.description = '';
            let filesError = false;
            if (!this.importZipFile.type.includes('zip')) {
                this.archiveErrors.push(this.$gettext('Die gewählte Datei ist kein Archiv.'));
                filesError = true;
            }
            if (!filesError) {
                try {
                    this.zip = await JSZip.loadAsync(this.importZipFile);
                } catch(error) {
                    this.zip = null;
                    this.archiveErrors.push(this.$gettext('Beim laden des Archivs ist ein Fehler aufgetreten. Vermutlich ist das Archiv beschädigt.'));
                    filesError = true;
                }
                
                if (this.zip) {
                    if (this.zip.file('courseware.json') === null) {
                        this.archiveErrors.push(this.$gettext('Das Archiv enthält keine courseware.json Datei.'));
                        filesError = true;
                    }
                    if (this.zip.file('files.json') === null) {
                        this.archiveErrors.push(this.$gettext('Das Archiv enthält keine files.json Datei.'));
                        filesError = true;
                    }
                    if (this.zip.file('data.xml') !== null) {
                        this.archiveErrors.push(this.$gettext(
                            'Das Archiv enthält eine data.xml Datei. Möglicherweise handelt es sich um einen Export aus dem Courseware-Plugin. Diese Archive sind nicht kompatibel mit dieser Courseware.'
                        ));
                        filesError = true;
                    }
                }
            }
            if (filesError) {
                this.updateRequirements(slot, text, false);
                slot.valid = false;
                return;
            } else {
                this.updateRequirements(slot, text, true);
                slot.valid = true;
            }

            let data = await this.zip.file('courseware.json').async('string');
            let courseware = null;
            let data_settings = null;
            let settings = null;
            let data_files = await this.zip.file('files.json').async('string');
            let files = null;
            let jsonErrors = false;

            try {
                courseware = JSON.parse(data);
            } catch (error) {
                jsonErrors = true;
                this.archiveErrors.push(this.$gettext('Die Beschreibung der Courseware-Inhalte ist nicht valide.'));
                this.archiveErrors.push(error);
            }

            if (this.zip.file('settings.json') !== null) {
                data_settings = await this.zip.file('settings.json').async('string');
                try {
                    settings = JSON.parse(data_settings);
                } catch (error) {
                    jsonErrors = true;
                    this.archiveErrors.push(this.$gettext('Die Beschreibung der Courseware-Einstellungen ist nicht valide.'));
                    this.archiveErrors.push(error);
                }
            }

            try {
                files = JSON.parse(data_files);
            } catch (error) {
                jsonErrors = true;
                this.archiveErrors.push(this.$gettext('Die Beschreibung der Dateien ist nicht valide.'));
                this.archiveErrors.push(error);
            }
            if (jsonErrors) {
                return;
            }

            this.loadedZipData = {
                courseware: courseware,
                files: files,
                settings: settings
            }

            this.modifiedData.title = courseware.attributes.title;
            this.modifiedData.color = courseware.attributes.payload.color ?? 'studip-blue';
            this.modifiedData.description = courseware.attributes.payload.description ?? '';
        },

        async importCoursewareArchiv() {
            if (this.loadedZipData === null) {
                return false;
            }

            this.setImportFilesProgress(0);
            this.setImportStructuresProgress(0);
            this.setImportErrors([]);

            this.importRunning = true;

            const title = this.modifiedData.title !==  '' ? this.modifiedData.title : this.loadedTitle;
            const description = this.modifiedData.description !==  '' ? this.modifiedData.description : this.loadedDescription;

            const unit = {
                    attributes: {
                        title: title,
                        payload: {
                            description: description,
                            color: this.modifiedData.color,
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
            await this.createCoursewareUnit(unit, { root: true });
            const newElementId = this.lastCreateCoursewareUnit.relationships['structural-element'].data.id;
            await this.loadStructuralElementById({ id: newElementId });

            const newStructuralElement = this.structuralElementById({id: newElementId});

            await this.importCourseware(this.loadedZipData.courseware, newStructuralElement.id, this.loadedZipData.files, 'migrate', this.loadedZipData.settings);
            this.companionSuccess({ info: this.$gettext('Lernmaterial importiert.') });
        },
        updateRequirements(slot, text, valid) {
            const index = this.requirements.findIndex(req =>  req.slot.id === slot.id && req.text === text);
            if (valid) {
                if (index !== -1) {
                    this.requirements.splice(index, 1);
                }
            } else {
                if (index === -1) {
                   this.requirements.push({slot: slot, text: text});
                }
            }
        }
    }
}
</script>