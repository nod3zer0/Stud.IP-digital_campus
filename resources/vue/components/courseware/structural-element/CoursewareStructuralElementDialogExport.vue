<template>
    <studip-dialog
        :title="$gettext('Seite exportieren')"
        :confirmText="this.$gettext('Erstellen')"
        confirmClass="accept"
        :closeText="this.$gettext('Schließen')"
        closeClass="cancel"
        height="350"
        @close="showElementExportDialog(false)"
        @confirm="exportStructuralElement"
    >
        <template v-slot:dialogContent>
            <div v-show="!exportRunning">
                {{
                    $gettextInterpolate($gettext('Hiermit exportieren Sie die Seite "%{ pageTitle }" als ZIP-Datei.'), {
                        pageTitle: structuralElement.attributes.title,
                    })
                }}
                <div class="cw-element-export">
                    <label>
                        <input type="checkbox" v-model="exportChildren">
                        {{ $gettext('Unterseiten exportieren') }}
                    </label>
                </div>
            </div>

            <courseware-companion-box
                v-show="exportRunning"
                :msgCompanion="$gettext('Export läuft, bitte haben sie einen Moment Geduld...')"
                mood="pointing"
            />
            <div v-show="exportRunning" class="cw-import-zip">
                <header>{{ exportState }}:</header>
                <div class="progress-bar-wrapper">
                    <div
                        class="progress-bar"
                        role="progressbar"
                        :style="{ width: exportProgress + '%' }"
                        :aria-valuenow="exportProgress"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        {{ exportProgress }}%
                    </div>
                </div>
            </div>
        </template>
    </studip-dialog>
</template>
<script>
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-dialog-export',
    mixins: [CoursewareExport],
    components: { CoursewareCompanionBox },
    props: {
        structuralElement: Object,
    },
    data() {
        return {
            exportRunning: false,
            exportChildren: false,
        };
    },
    computed: {
        ...mapGetters({
            exportState: 'exportState',
            exportProgress: 'exportProgress',
        }),
    },
    methods: {
        ...mapActions({
            showElementExportDialog: 'showElementExportDialog',
        }),
        async exportStructuralElement(data) {
            if (this.exportRunning) {
                return;
            }

            this.exportRunning = true;

            await this.sendExportZip(this.structuralElement.id, {
                withChildren: this.exportChildren,
            });

            this.exportRunning = false;
            this.showElementExportDialog(false);
        },
    },
};
</script>
