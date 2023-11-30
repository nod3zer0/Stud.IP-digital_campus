<template>
    <studip-dialog
        :title="$gettext('Seite exportieren')"
        :confirmText="this.$gettext('Erstellen')"
        confirmClass="accept"
        :closeText="this.$gettext('Schließen')"
        closeClass="cancel"
        height="350"
        @close="showElementPdfExportDialog(false)"
        @confirm="pdfExportCurrentElement"
    >
        <template v-slot:dialogContent>
            {{
                $gettextInterpolate($gettext('Hiermit exportieren Sie die Seite "%{ pageTitle }" als PDF-Datei.'), {
                    pageTitle: structuralElement.attributes.title,
                })
            }}
            <div class="cw-element-export">
                <label>
                    <input type="checkbox" v-model="pdfExportChildren">
                    {{ $gettext('Unterseiten exportieren') }}
                </label>
            </div>
        </template>
    </studip-dialog>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-dialog-export',
    props: {
        structuralElement: Object,
    },
    data() {
        return {
            pdfExportChildren: false,
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
        }),
    },
    methods: {
        ...mapActions({
            showElementPdfExportDialog: 'showElementPdfExportDialog',
        }),
        pdfExportCurrentElement() {
            this.showElementPdfExportDialog(false);
            let url = '';
            let withChildren = this.pdfExportChildren ? '/1' : '/0';
            if (this.context.type === 'users') {
                url = STUDIP.URLHelper.getURL(
                    'dispatch.php/contents/courseware/pdf_export/' + this.structuralElement.id + withChildren
                );
            }
            if (this.context.type === 'courses') {
                url = STUDIP.URLHelper.getURL(
                    'dispatch.php/course/courseware/pdf_export/' + this.structuralElement.id + withChildren
                );
            }

            if (url) {
                window.open(url, '_blank').focus();
            }
        },
    },
};
</script>
