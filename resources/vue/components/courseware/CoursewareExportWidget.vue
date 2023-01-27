<template>
    <sidebar-widget :title="$gettext('Export')" v-if="structuralElement">
        <template #content>
            <ul class="widget-list widget-links cw-export-widget" v-if="structuralElement">
                <li v-if="showExportArchiv" class="cw-export-widget-export">
                    <button @click="exportElement">
                        {{ $gettext('Seiten exportieren') }}
                    </button>
                </li>
                <li v-if="showExportPdf" class="cw-export-widget-export-pdf">
                    <button @click="pdfElement">
                        {{ $gettext('PDF-Dokument erstellen') }}
                    </button>
                </li>
                <li v-if="showOer" class="cw-export-widget-oer">
                    <button @click="oerElement">
                        {{ $gettext('Auf OER Campus veröffentlichen') }}
                    </button>
                </li>
                <li v-if="!showExportArchiv && !showExportPdf && !showOer">
                    {{ $gettext('Keine Exportoptionen verfügbar') }}
                </li>
            </ul>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-export-widget',
    props: ['structuralElement', 'canVisit'],
    components: {
        SidebarWidget
    },
    mixins: [CoursewareExport],
    computed: {
        ...mapGetters({
            context: 'context',
            oerEnabled: 'oerEnabled',
            userIsTeacher: 'userIsTeacher',
        }),
        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
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
            if (this.context.type === 'users') {
                return true;
            }

            return this.oerEnabled && this.userIsTeacher && this.canVisit
        }
    },
    methods: {
        ...mapActions({
            showElementExportDialog: 'showElementExportDialog',
            showElementPdfExportDialog: 'showElementPdfExportDialog',
            showElementOerDialog: 'showElementOerDialog',
        }),
        exportElement() {
            this.showElementExportDialog(true);
        },
        pdfElement() {
            this.showElementPdfExportDialog(true);
        },
        oerElement() {
            this.showElementOerDialog(true);
        }
    },
};
</script>
