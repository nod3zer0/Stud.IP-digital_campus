<template>
    <ul class="widget-list widget-links cw-export-widget" v-if="structuralElement">
        <li v-if="showExportArchiv" class="cw-export-widget-export">
            <button @click="exportElement">
                <translate>Seite exportieren</translate>
            </button>
        </li>
        <li v-if="showExportPdf" class="cw-export-widget-export-pdf">
            <a :href="pdfExportURL" target="_blank">
                <translate>Seite als pdf-Dokument exportieren</translate>
            </a>
        </li>
        <li v-if="showOer" class="cw-export-widget-oer">
            <button @click="oerElement">
                <translate>Seite auf %{oerTitle} veröffentlichen</translate>
            </button>
        </li>
        <li v-if="!showExportArchiv && !showExportPdf && !showOer">
            <translate>Keine Exportoptionen verfügbar</translate>
        </li>
    </ul>
</template>

<script>
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-export-widget',
    props: ['structuralElement', 'canVisit'],
    mixins: [CoursewareExport],
    computed: {
        ...mapGetters({
            context: 'context',
            oerEnabled: 'oerEnabled',
            oerTitle: 'oerTitle',
            userIsTeacher: 'userIsTeacher',
        }),
        pdfExportURL() {
            if (this.context.type === 'users') {
                return STUDIP.URLHelper.getURL('dispatch.php/contents/courseware/pdf_export/' + this.structuralElement.id);
            }
            if (this.context.type === 'courses') {
                return STUDIP.URLHelper.getURL('dispatch.php/course/courseware/pdf_export/' + this.structuralElement.id);
            }

            return '';
        },
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
            showElementOerDialog: 'showElementOerDialog',
        }),
        exportElement() {
            this.showElementExportDialog(true);
        },
        oerElement() {
            this.showElementOerDialog(true);
        }
    },
};
</script>
