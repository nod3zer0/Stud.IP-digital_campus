<template>
    <studip-dialog
        :title="$gettext('Seiten importieren')"
        :confirmText="$gettext('Importieren')"
        :confirmDisabled="importRunning || importAborted"
        :closeText="importRunning || importAborted ? $gettext('Schließen') : $gettext('Abbrechen')"
        height="420"
        @close="showElementImportDialog(false)"
        @confirm="importCoursewareArchiv"
    >
        <template v-slot:dialogContent>
            <form v-if="!importRunning && !importAborted" class="default" @submit.prevent="">
                <label>
                    {{$gettext('Importdatei')}}
                    <input class="cw-file-input" ref="importFile" type="file" accept=".zip" @change="setImport" />
                </label>
                <label>
                {{$gettext('Importverhalten')}}
                <select v-model="importBehavior">
                    <option value="default">{{$gettext('Inhalte anhängen')}}</option>
                    <option value="migrate">{{$gettext('Inhalte zusammenführen')}}</option>
                </select>
            </label>
            </form>
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
                    v-show="!importDone && importRunning"
                    :msgCompanion="$gettext('Import läuft. Bitte schließen Sie den Dialog nicht bis der Import abgeschlossen wurde.')"
                    mood="pointing"
                />
                <courseware-companion-box
                    v-show="importAborted"
                    :msgCompanion="$gettext('Import abgebrochen. Es sind Fehler aufgetreten!')"
                    mood="sad"
                />
            </div>
            <form v-if="!importDone && importRunning" class="default" @submit.prevent="">
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
            </form>
            <form v-if="importErrors.length > 0" class="default" @submit.prevent="">
                <fieldset>
                    <legend>{{$gettext('Fehlermeldungen')}}</legend>
                    <ul>
                        <li v-for="(error, index) in importErrors" :key="index"> {{error}} </li>
                    </ul>
                </fieldset>
            </form>
        </template>
    </studip-dialog>
</template>
<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareImport from '@/vue/mixins/courseware/import.js';

import { mapActions, mapGetters } from 'vuex'
import JSZip from 'jszip';

export default {
    name: 'courseware-structural-element-dialog-import',
    components: {
        CoursewareCompanionBox,
    },
    mixins: [CoursewareImport],
    props: {
        colors: Array
    },
    data() {
        return {
            importBehavior: 'default',
            importRunning: false,
            importZipFile: null,
            zip: null,
            importAborted: false,
        }
    },
    computed: {
        ...mapGetters({
            currentElement: 'currentElement',
            importFilesState: 'importFilesState',
            importFilesProgress: 'importFilesProgress',
            importStructuresState: 'importStructuresState',
            importStructuresProgress: 'importStructuresProgress',
            importErrors: 'importErrors',
        }),
        fileImportDone() {
            return this.importFilesProgress === 100;
        },
        importDone() {
            return (this.importFilesProgress === 100 && this.importStructuresProgress === 100);
        }
    },
    methods: {
        ...mapActions({
            showElementImportDialog: 'showElementImportDialog',
            loadCoursewareStructure: 'courseware-structure/load',
            setImportFilesProgress: 'setImportFilesProgress',
            setImportStructuresProgress: 'setImportStructuresProgress',
            setImportErrors: 'setImportErrors',
        }),
        setImport(event) {
            this.importZipFile = event.target.files[0];
            this.setImportFilesProgress(0);
            this.setImportStructuresProgress(0);
            this.setImportErrors([]);
        },
        async importCoursewareArchiv() {
            this.importAborted = false;
            if (this.importZipFile === null) {
                return false;
            }

            this.importRunning = true;
            try {
                this.zip = await JSZip.loadAsync(this.importZipFile);
            } catch(error) {
                this.setImportErrors([this.$gettext('Die gewählte Datei ist kein Archiv oder das Archiv ist beschädigt.')]);
                this.importRunning = false;
                this.importAborted = true;
                return;
            }
            let errors = [];
            let missingFiles = false;
            if (this.zip.file('courseware.json') === null) {
                errors.push(this.$gettext('Das Archiv enthält keine courseware.json Datei.'));
                missingFiles = true;
            }
            if (this.zip.file('files.json') === null) {
                errors.push(this.$gettext('Das Archiv enthält keine files.json Datei.'));
                missingFiles = true;
            }
            if (this.zip.file('data.xml') !== null) {
                errors.push(this.$gettext(
                    'Das Archiv enthält eine data.xml Datei. Möglicherweise handelt es sich um einen Export aus dem Courseware-Plugin. Diese Archive sind nicht kompatibel mit dieser Courseware.'
                ));
            }
            if (missingFiles) {
                this.setImportErrors(errors);
                this.importRunning = false;
                this.importAborted = true;
                return;
            }

            const data = await this.zip.file('courseware.json').async('string');
            let courseware = null;
            const data_files = await this.zip.file('files.json').async('string');
            let files = null;
            let jsonErrors = false;
            try {
                courseware = JSON.parse(data);
            } catch (error) {
                jsonErrors = true;
                errors.push(this.$gettext('Die Beschreibung der Courseware-Inhalte ist nicht valide.'));
                errors.push(error);
            }
            try {
                files = JSON.parse(data_files);
            } catch (error) {
                jsonErrors = true;
                errors.push(this.$gettext('Die Beschreibung der Dateien ist nicht valide.'));
                errors.push(error);
            }
            if (jsonErrors) {
                this.setImportErrors(errors);
                this.importRunning = false;
                this.importAborted = true;
                return;
            }

            await this.loadCoursewareStructure();

            await this.importCourseware(courseware, this.currentElement, files, this.importBehavior, null);
        }
    }
}
</script>