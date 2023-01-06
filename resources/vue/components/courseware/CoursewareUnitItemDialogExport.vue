<template>
        <studip-dialog
            :title="$gettext('Lernmaterial exportieren')"
            :confirmText="$gettext('Exportieren')"
            confirmClass="accept"
            :closeText="$gettext('Schließen')"
            closeClass="cancel"
            height="350"
            @close="$emit('close')"
            @confirm="executeExport"
        >
            <template v-slot:dialogContent>
                <courseware-companion-box
                    v-show="!exportRunning"
                    :msgCompanion="$gettextInterpolate($gettext('Export des Lernmaterials: %{title}'), {title: title})"
                    mood="curious"
                />

                <courseware-companion-box
                    v-show="exportRunning"
                    :msgCompanion="$gettextInterpolate($gettext('%{title} wird exportiert, bitte haben sie einen Moment Geduld...'), {title: title})"
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
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-unit-item-dialog-export',
    mixins: [CoursewareExport],
    components: {
        CoursewareCompanionBox,
    },
    props: {
        unit: Object
    },
    data() {
        return {
            currentInstance: null,
            exportRunning: false,
        }
    },
    computed: {
        ...mapGetters({
            context: 'context',
            exportProgress: 'exportProgress',
            exportState: 'exportState',
            instanceById: 'courseware-instances/byId',
            structuralElementById: 'courseware-structural-elements/byId',
            userIsTeacher: 'userIsTeacher', 
        }),
        instance() {
            if (this.inCourseContext) {
                return this.instanceById({id: 'course_' + this.context.id + '_' + this.unit.id});
            } else {
                return this.instanceById({id: 'user_' + this.context.id + '_' + this.unit.id});
            }
            
        },
        inCourseContext() {
            return this.context.type === 'courses';
        },
        unitElement() {
            return this.structuralElementById({id: this.unit.relationships['structural-element'].data.id}) ?? null;
        },
        title() {
            return  this.unitElement?.attributes?.title ?? '';
        },
    },
    methods: {
        ...mapActions({
            loadInstance: 'loadInstance',
            setExportState: 'setExportState',
            companionSuccess: 'companionSuccess'
        }),
        async loadUnitInstance() {
            const context = {type: this.context.type, id: this.context.id, unit: this.unit.id};
            await this.loadInstance(context);
        },
        async executeExport() {
            if (this.exportRunning) {
                return;
            }

            this.exportRunning = true;
            
            this.setExportState(this.$gettext('Lade Einstellungen'));
            await this.loadUnitInstance();
            this.setExportState('');

            await this.sendExportZip(this.unitElement.id, {
                withChildren: true,
                completeExport: true,
                settings: {
                    'editing-permission-level': this.instance.attributes['editing-permission-level'] ?? 'tutor',
                    'sequential-progression': this.instance.attributes['sequential-progression'] ?? 0,
                    'certificate-settings': this.instance.attributes['certificate-settings'],
                    'reminder-settings': this.instance.attributes['reminder-settings'],
                    'reset-progress-settings': this.instance.attributes['reset-progress-settings']
                }
            });

            this.exportRunning = false;
            this.$emit('close');
        },

    },
    async mounted() {
        await this.loadUnitInstance();
    }
}
</script>