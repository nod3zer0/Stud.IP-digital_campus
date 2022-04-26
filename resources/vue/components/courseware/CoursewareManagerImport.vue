<template>
  <div>
    <courseware-companion-box v-show="!importRunning && importDone && importErrors.length === 0" :msgCompanion="$gettext('Import erfolgreich!')" mood="special"/>
    <courseware-companion-box v-show="!importRunning && importDone && importErrors.length > 0" :msgCompanion="$gettext('Import abgeschlossen. Es sind Fehler aufgetreten!')" mood="unsure"/>
    <courseware-companion-box v-show="!importRunning && !importDone && importErrors.length > 0" :msgCompanion="$gettext('Import fehlgeschlagen. Es sind Fehler aufgetreten!')" mood="sad"/>
    <courseware-companion-box v-show="importRunning" :msgCompanion="$gettext('Import läuft. Bitte verlassen Sie die Seite nicht bis der Import abgeschlossen wurde.')" mood="pointing"/>
    <form class="default" @submit.prevent="">

        <fieldset v-show="importRunning">
            <legend><translate>Import läuft...</translate></legend>
            <div v-if="importRunning" class="cw-import-zip">
                <header><translate>Importiere Dateien</translate>:</header>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar" role="progressbar" :style="{width: importFilesProgress + '%'}" :aria-valuenow="importFilesProgress" aria-valuemin="0" aria-valuemax="100">{{ importFilesProgress }}%</div>
                </div>
                {{ importFilesState }}
            </div>
            <div v-if="fileImportDone && importRunning" class="cw-import-zip">
                <header><translate>Importiere Elemente</translate>:</header>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar" role="progressbar" :style="{width: importStructuresProgress + '%'}" :aria-valuenow="importStructuresProgress" aria-valuemin="0" aria-valuemax="100">{{ importStructuresProgress }}%</div>
                </div>
                {{ importStructuresState }}
            </div>
        </fieldset>
        <fieldset v-show="importErrors.length > 0">
            <legend><translate>Fehlermeldungen</translate></legend>
            <ul>
                <li v-for="(error, index) in importErrors" :key="index"> {{error}} </li>
            </ul>
        </fieldset>
        <fieldset v-show="!importRunning">
            <legend><translate>Import</translate></legend>
            <label>
                <translate>Importdatei</translate>
                <input class="cw-file-input" ref="importFile" type="file" accept=".zip" @change="setImport" />
            </label>
            <label>
                <translate>Importverhalten</translate>
                <select v-model="importBehavior">
                    <option value="default"><translate>Inhalte anhängen</translate></option>
                    <option value="migrate"><translate>Inhalte zusammenführen</translate></option>
                </select>
            </label>
        </fieldset>
        <footer v-show="!importRunning">
            <button
                class="button"
                @click.prevent="doImportCourseware"
                :disabled="!importZip"
            >
                <translate>Importieren</translate>
            </button>
        </footer>
    </form>
  </div>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

import CoursewareImport from '@/vue/mixins/courseware/import.js';
import { mapActions, mapGetters } from 'vuex';
import JSZip from 'jszip';

export default {
    name: 'courseware-manager-import',
    components: {
        CoursewareCompanionBox,
    },
    mixins: [CoursewareImport],
    data() {
        return {
            importBehavior: 'default',
            importRunning: false,
            importZip: null,
            zip: null
        }
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware',
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
            return this.importFilesProgress === 100 && this.importStructuresProgress === 100;
        }
    },
    methods: {
        ...mapActions({
            loadCoursewareStructure: 'courseware-structure/load',
            setImportFilesProgress: 'setImportFilesProgress',
            setImportStructuresProgress: 'setImportStructuresProgress',
            setImportErrors: 'setImportErrors',
        }),

        setImport(event) {
            this.importZip = event.target.files[0];
            this.setImportFilesProgress(0);
            this.setImportStructuresProgress(0);
            this.setImportErrors([]);
        },

        async doImportCourseware() {
            if (this.importZip === null) {
                return false;
            }

            this.importRunning = true;

            let view = this;

            view.zip = new JSZip();

            await view.zip.loadAsync(this.importZip).then(async function () {
                let errors = [];
                let missingFiles = false;
                if (view.zip.file('courseware.json') === null) {
                    errors.push(view.$gettext('Das Archiv enthält keine courseware.json Datei.'));
                    missingFiles = true;
                }
                if (view.zip.file('files.json') === null) {
                    errors.push(view.$gettext('Das Archiv enthält keine files.json Datei.'));
                    missingFiles = true;
                }
                if (view.zip.file('data.xml') !== null) {
                    errors.push(view.$gettext(
                        'Das Archiv enthält eine data.xml Datei. Möglicherweise handelt es sich um einen Export aus dem Courseware-Plugin. Diese Archive sind nicht kompatibel mit dieser Courseware.'
                    ));
                }
                if (missingFiles) {
                    view.setImportErrors(errors);
                    return;
                }

                let data = await view.zip.file('courseware.json').async('string');
                let courseware = null;
                let data_files = await view.zip.file('files.json').async('string');
                let files = null;
                let jsonErrors = false;
                try {
                    courseware = JSON.parse(data);
                } catch (error) {
                    jsonErrors = true;
                    errors.push(view.$gettext('Die Beschreibung der Courseware-Inhalte ist nicht valide.'));
                    errors.push(error);
                }
                try {
                    files = JSON.parse(data_files);
                } catch (error) {
                    jsonErrors = true;
                    errors.push(view.$gettext('Die Beschreibung der Dateien ist nicht valide.'));
                    errors.push(error);
                }
                if (jsonErrors) {
                    view.setImportErrors(errors);
                    return;
                }

                await view.loadCoursewareStructure();
                const rootId = view.courseware.relationships.root.data.id;

                await view.importCourseware(courseware, rootId, files, view.importBehavior);
            });

            this.importZip = null;
            this.importRunning = false;
            this.$refs.importFile.value = '';
        },

        getFileSizeText(size) {
            if (size / 1024 < 1000) {
                return (size / 1024).toFixed(2) + ' kB';
            } else {
                return (size / 1048576).toFixed(2) + ' MB';
            }
        },
    },
    mounted() {
        let view = this;

        window.onbeforeunload = function() {
            return view.importRunning ? true : null
        }
    }
}
</script>
