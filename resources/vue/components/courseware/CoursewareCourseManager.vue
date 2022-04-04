<template>
    <div class="cw-course-manager-wrapper">
        <div class="cw-course-manager">
            <courseware-tabs class="cw-course-manager-tabs">
                <courseware-tab :name="$gettext('Diese Courseware')" :selected="true" :index="0">
                    <courseware-manager-element
                        type="current"
                        :currentElement="currentElement"
                        @selectElement="setCurrentId"
                        @reloadElement="reloadElements"
                    />
                </courseware-tab>
                <courseware-tab :name="$gettext('Export')" :index="1">
                    <button
                        class="button"
                        @click.prevent="doExportCourseware"
                        :class="{
                            disabled: exportRunning,
                        }"
                    >
                        <translate>Alles exportieren</translate>
                    </button>
                    <courseware-companion-box v-show="exportRunning" :msgCompanion="$gettext('Export läuft, bitte haben sie einen Moment Geduld...')" mood="pointing"/>
                    <div v-if="exportRunning" class="cw-import-zip">
                        <header>{{exportState}}:</header>
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar" role="progressbar" :style="{width: exportProgress + '%'}" :aria-valuenow="exportProgress" aria-valuemin="0" aria-valuemax="100">{{ exportProgress }}%</div>
                        </div>
                    </div>
                </courseware-tab>
            </courseware-tabs>

            <courseware-tabs class="cw-course-manager-tabs">
                <courseware-tab :name="$gettext('FAQ')" :index="0">
                    <courseware-collapsible-box :open="true" :title="$gettext('Wie finde ich die gewünschte Stelle?')">
                        <p><translate>
                            Wählen Sie auf der linken Seite "Diese Courseware" aus.
                            Beim Laden der Seite ist dies immer gewählt. Die Überschrift
                            gibt an, welche Seite Sie gerade ausgewählt haben. Darunter befinden
                            sich die Abschnitte der Seite und innerhalb dieser deren Blöcke.
                            Möchten Sie eine Seite, die unterhalb der gewählten liegt, bearbeiten,
                            können Sie diese über die Schaltflächen im Bereich "Unterseiten" wählen.
                            Über der Überschrift wird eine Navigation eingeblendet, mit der Sie beliebig
                            weit hoch in der Hierarchie springen können.
                        </translate></p>
                    </courseware-collapsible-box>
                    <courseware-collapsible-box :title="$gettext('Wie sortiere ich Objekte?')">
                        <p><translate>
                            Seiten, Abschnitte und Blöcke lassen sich in ihrer Reihenfolge sortieren.
                            Hierzu wählen Sie auf der linken Seite unter "Diese Courseware" die Schaltfläche "Unterseiten sortieren",
                            "Abschnitte sortieren" oder "Blöcke sortieren".
                            An den Objekten werden Pfeile angezeigt, mit diesen können die Objekte an die gewünschte
                            Position gebracht werden. Um die neue Sortierung zu speichern, wählen Sie "Sortieren beenden".
                            Sie können die Änderungen auch rückgängig machen, indem Sie "Sortieren abbrechen" wählen.
                        </translate></p>
                    </courseware-collapsible-box>
                    <courseware-collapsible-box :title="$gettext('Wie verschiebe ich Objekte?')">
                        <p><translate>
                            Seiten, Abschnitte und Blöcke lassen sich verschieben.
                            Hierzu wählen Sie auf der linken Seite unter "Diese Courseware" die Schaltfläche
                            "Seite an dieser Stelle einfügen", "Abschnitt an dieser Stelle einfügen" oder
                            "Block an dieser Stelle einfügen". Wählen Sie dann auf der rechten Seite unter
                            "Verschieben" das Objekt aus, das Sie verschieben möchten. Verschiebbare Objekte
                            erkennen Sie an den zwei nach links zeigenden gelben Pfeilen.
                        </translate></p>
                    </courseware-collapsible-box>
                    <courseware-collapsible-box :title="$gettext('Wie kopiere ich Objekte?')">
                        <p><translate>
                            Seiten, Abschnitte und Blöcke lassen sich aus einer anderen Veranstaltung und Ihren
                            eigenen Inhalten kopieren.
                            Hierzu wählen Sie auf der linken Seite unter "Diese Courseware" die Schaltfläche
                            "Seite an dieser Stelle einfügen", "Abschnitt an dieser Stelle einfügen" oder
                            "Block an dieser Stelle einfügen". Wählen Sie dann auf der rechten Seite unter
                            "Kopieren" erst die Veranstaltung aus der Sie kopieren möchten oder Ihre eigenen
                            Inhalte. Wählen sie dann das Objekt aus, das Sie kopieren möchten. Kopierbare Objekte
                            erkennen Sie an den zwei nach links zeigenden gelben Pfeilen.
                        </translate></p>
                    </courseware-collapsible-box>
                </courseware-tab>
                <courseware-tab :name="$gettext('Verschieben')" :selected="true" :index="1">
                    <courseware-manager-element
                    type="self"
                    :currentElement="selfElement"
                    :moveSelfPossible="moveSelfPossible"
                    :moveSelfChildPossible="moveSelfChildPossible"
                    @selectElement="setSelfId"
                    @reloadElement="reloadElements"
                    />
                </courseware-tab>

                <courseware-tab :name="$gettext('Kopieren')"  :index="2">
                    <courseware-manager-copy-selector @loadSelf="reloadElements" @reloadElement="reloadElements" />
                </courseware-tab>

                <courseware-tab :name="$gettext('Importieren')"  :index="3">
                    <courseware-companion-box v-show="!importRunning && importDone" :msgCompanion="$gettext('Import erfolgreich!')" mood="special"/>
                    <courseware-companion-box v-show="importRunning" :msgCompanion="$gettext('Import läuft. Bitte verlassen Sie die Seite nicht bis der Import abgeschlossen wurde.')" mood="pointing"/>
                    <button
                        v-show="!importRunning"
                        class="button"
                        @click.prevent="chooseFile"
                    >
                        <translate>Importdatei auswählen</translate>
                    </button>

                    <div v-if="importZip" class="cw-import-zip">
                        <header>{{ importZip.name }}</header>
                        <p><translate>Größe</translate>: {{ getFileSizeText(importZip.size) }}</p>
                    </div>

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

                    <button
                        v-show="importZip && !importRunning"
                        class="button"
                        @click.prevent="doImportCourseware"
                    >
                        <translate>Alles importieren</translate>
                    </button>

                    <ul v-if="importErrors.length > 0">
                        <li v-for="(index, error) in importErrors" :key="index"> {{error}} </li>
                    </ul>

                    <input ref="importFile" type="file" accept=".zip" @change="setImport" style="visibility: hidden" />
                </courseware-tab>
                <courseware-tab v-if="context.type === 'courses'" :name="$gettext('Aufgabe verteilen')"  :index="4">
                    <courseware-manager-task-distributor />
                </courseware-tab>
            </courseware-tabs>
        </div>
        <courseware-companion-overlay />
    </div>
</template>
<script>
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareManagerElement from './CoursewareManagerElement.vue';
import CoursewareManagerCopySelector from './CoursewareManagerCopySelector.vue';
import CoursewareManagerTaskDistributor from './CoursewareManagerTaskDistributor.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareImport from '@/vue/mixins/courseware/import.js';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

import JSZip from 'jszip';
import FileSaver from 'file-saver';

export default {
    name: 'courseware-course-manager',
    components: {
        CoursewareTabs,
        CoursewareTab,
        CoursewareCollapsibleBox,
        CoursewareManagerElement,
        CoursewareManagerCopySelector,
        CoursewareCompanionOverlay,
        CoursewareCompanionBox,
        CoursewareManagerTaskDistributor
    },

    mixins: [CoursewareImport, CoursewareExport],

    data() {
        return {
            exportRunning: false,
            importRunning: false,
            importZip: null,
            currentElement: {},
            currentId: null,
            selfElement: {},
            selfId: null,
            zip: null
        };
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            context: 'context',
            structuralElementById: 'courseware-structural-elements/byId',
            importFilesState: 'importFilesState',
            importFilesProgress: 'importFilesProgress',
            importStructuresState: 'importStructuresState',
            importStructuresProgress: 'importStructuresProgress',
            importErrors: 'importErrors',
            exportState: 'exportState',
            exportProgress: 'exportProgress'
        }),
        moveSelfPossible() {
            if (this.selfElement.relationships === undefined) {
                return false
            } else if (this.selfElement.relationships.parent.data === null) {
                return false;
            } else if (this.currentElement.id === this.selfElement.relationships.parent.data.id) {
                return false;
            } else if (this.currentId === this.selfId) {
                return false;
            } else {
                return true;
            }
        },
        moveSelfChildPossible() {
            return this.currentId !== this.selfId;
        },
        fileImportDone() {
            return this.importFilesProgress === 100;
        },
        importDone() {
            return this.importFilesProgress === 100 && this.importStructuresProgress === 100;
        }
    },

    methods: {
        ...mapActions({
            loadCoursewareStructure: 'loadCoursewareStructure',
            createStructuralElement: 'createStructuralElement',
            updateStructuralElement: 'updateStructuralElement',
            deleteStructuralElement: 'deleteStructuralElement',
            loadStructuralElement: 'loadStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            addBookmark: 'addBookmark',
            companionInfo: 'companionInfo',
            setImportFilesProgress: 'setImportFilesProgress',
            setImportStructuresProgress: 'setImportStructuresProgress',
        }),
        async reloadElements() {
            await this.setCurrentId(this.currentId);
            await this.setSelfId(this.selfId);
            this.$emit("reload");
        },
        async setCurrentId(target) {
            this.currentId = target;
            await this.loadStructuralElement(this.currentId);
            this.initCurrent();
        },
        initCurrent() {
            this.currentElement = _.cloneDeep(this.structuralElementById({ id: this.currentId }));
        },
        async setSelfId(target) {
            this.selfId = target;
            await this.loadStructuralElement(this.selfId);
            this.initSelf();
        },
        initSelf() {
            this.selfElement = _.cloneDeep(this.structuralElementById({ id: this.selfId }));
        },

        async doExportCourseware() {
            if (this.exportRunning) {
                return false;
            }

            this.exportRunning = true;

            await this.loadCoursewareStructure();
            await this.sendExportZip(
                this.courseware.relationships.root.data.id,
                {withChildren: true}
            );

            this.exportRunning = false;
        },

        setImport(event) {
            this.importZip = event.target.files[0];
        },

        async doImportCourseware() {
            if (this.importZip === null) {
                return false;
            }

            this.importRunning = true;

            let view = this;

            view.zip = new JSZip();

            await view.zip.loadAsync(this.importZip).then(async function () {
                let data = await view.zip.file('courseware.json').async('string');
                let courseware = JSON.parse(data);

                let data_files = await view.zip.file('files.json').async('string');
                let files = JSON.parse(data_files);

                await view.loadCoursewareStructure();
                let parent_id = view.courseware.relationships.root.data.id;

                await view.importCourseware(courseware, parent_id, files);
            });

            this.importZip = null;
            this.importRunning = false;
        },

        chooseFile() {
            this.$refs.importFile.click();
            this.setImportFilesProgress(0);
            this.setImportStructuresProgress(0);
        },
        getFileSizeText(size) {
            if (size / 1024 < 1000) {
                return (size / 1024).toFixed(2) + ' kB';
            } else {
                return (size / 1048576).toFixed(2) + ' MB';
            }
        }
    },
    watch: {
        courseware(newValue, oldValue) {
            let currentId = newValue.relationships.root.data.id;
            this.setCurrentId(currentId);
            this.setSelfId(currentId);
        },
    },
};
</script>
