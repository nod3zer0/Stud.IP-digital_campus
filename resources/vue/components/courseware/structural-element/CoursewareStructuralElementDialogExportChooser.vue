<template>
    <studip-dialog
        :title="$gettext('Seite exportieren')"
        :closeText="$gettext('Schließen')"
        closeClass="cancel"
        height="320"
        width="610"
        @close="showElementExportChooserDialog(false)"
    >
        <template v-slot:dialogContent>
            <div class="square-button-panel">
                <studip-square-button
                    v-if="showExportArchiv"
                    icon="file-archive"
                    :title="$gettext('ZIP Datei herunterladen')"
                    @click="selectType('archiv')"
                />
                <studip-square-button
                    v-if="showExportPdf"
                    icon="file-pdf"
                    :title="$gettext('PDF Datei herunterladen')"
                    @click="selectType('pdf')"
                />
                <studip-square-button
                    v-if="showOer"
                    icon="oer-campus"
                    :title="$gettext('Auf OER Campus veröffentlichen')"
                    @click="selectType('oer')"
                />
            </div>
            <courseware-companion-box
                v-if="!showExportArchiv && !showExportPdf && !showOer"
                mood="pointing"
                :msgCompanion="$gettext('Keine Exportoptionen verfügbar.')"
            />
        </template>
    </studip-dialog>
</template>

<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import StudipSquareButton from './../../StudipSquareButton.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-dialog-export-chooser',
    props: ['canEdit', 'canVisit'],
    components: {
        CoursewareCompanionBox,
        StudipSquareButton,
    },
    computed: {
        ...mapGetters({
            context: 'context',
            oerCampusEnabled: 'oerCampusEnabled',
            userIsTeacher: 'userIsTeacher',
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        },
        showExportArchiv() {
            if (this.context.type === 'users') {
                return true;
            }

            return this.canEdit;
        },
        showExportPdf() {
            if (this.context.type === 'users') {
                return true;
            }

            return this.canVisit;
        },
        showOer() {
            if (!this.oerCampusEnabled) {
                return false;
            }

            if (this.context.type === 'users') {
                return true;
            }

            return this.userIsTeacher && this.canVisit;
        },
    },
    methods: {
        ...mapActions({
            showElementExportDialog: 'showElementExportDialog',
            showElementExportChooserDialog: 'showElementExportChooserDialog',
            showElementPdfExportDialog: 'showElementPdfExportDialog',
            showElementOerDialog: 'showElementOerDialog',
        }),
        selectType(type) {
            switch (type) {
                case 'archiv':
                    this.showElementExportDialog(true);
                    break;
                case 'pdf':
                    this.showElementPdfExportDialog(true);
                    break;
                case 'oer':
                    this.showElementOerDialog(true);
                    break;
            }
            this.showElementExportChooserDialog(false);
        },
    },
};
</script>
<style scoped lang="scss">
.square-button-panel {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    justify-content: center;
}
</style>
