<template>
    <ul class="widget-list widget-links cw-export-widget" v-if="structuralElement">
        <li class="cw-export-widget-export">
            <a href="#" @click="exportElement">
                <translate>Seite exportieren</translate>
            </a>
        </li>
        <li v-if="canVisit" class="cw-export-widget-export-pdf">
            <a :href="pdfExportURL">
                <translate>Seite als pdf-Dokument exportieren</translate>
            </a>
        </li>
        <li v-if="oerEnabled" class="cw-export-widget-oer">
            <a href="#" @click="oerElement">
                <translate>Seite auf %{oerTitle} veröffentlichen</translate>
            </a>
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
