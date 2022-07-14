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

                <courseware-tab :name="$gettext('Verknüpfen')" :index="3">
                    <courseware-manager-link-selector @loadSelf="reloadElements" @reloadElement="reloadElements" />
                </courseware-tab>

                <courseware-tab :name="$gettext('Importieren')" :index="4">
                    <courseware-manager-import />
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
import CoursewareManagerLinkSelector from './CoursewareManagerLinkSelector.vue';
import CoursewareManagerTaskDistributor from './CoursewareManagerTaskDistributor.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareManagerImport from './CoursewareManagerImport.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-course-manager',
    components: {
        CoursewareTabs,
        CoursewareTab,
        CoursewareCollapsibleBox,
        CoursewareManagerElement,
        CoursewareManagerCopySelector,
        CoursewareManagerLinkSelector,
        CoursewareCompanionOverlay,
        CoursewareCompanionBox,
        CoursewareManagerTaskDistributor,
        CoursewareManagerImport
    },

    mixins: [CoursewareExport],

    data() {
        return {
            exportRunning: false,
            currentElement: {},
            currentId: null,
            selfElement: {},
            selfId: null,
        };
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            context: 'context',
            structuralElementById: 'courseware-structural-elements/byId',
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
    },

    methods: {
        ...mapActions({
            loadCoursewareStructure: 'courseware-structure/load',
            createStructuralElement: 'createStructuralElement',
            updateStructuralElement: 'updateStructuralElement',
            deleteStructuralElement: 'deleteStructuralElement',
            loadStructuralElement: 'loadStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            addBookmark: 'addBookmark',
            companionInfo: 'companionInfo',
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
