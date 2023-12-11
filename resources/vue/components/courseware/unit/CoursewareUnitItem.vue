<template>
    <li class="courseware-unit-item">
        <courseware-tile
            tag="div"
            :color="color"
            :title="title"
            :descriptionLink="url"
            :descriptionTitle="$gettext('Lernmaterial öffnen')"
            :displayProgress="inCourseContext"
            :progress="progress"
            :imageUrl="imageUrl"
            :handle="handle"
            :handleId="'unit-handle-' + unit.id"
            @handle-keydown="$emit('unit-keydown', $event)"
        >
            <template #image-overlay-with-action-menu>
                <studip-action-menu
                    class="cw-unit-action-menu"
                    :items="menuItems"
                    :context="title"
                    :collapseAt="0"
                    @showDelete="openDeleteDialog"
                    @showExport="openExportDialog"
                    @showProgress="openProgressDialog"
                    @showSettings="openSettingsDialog"
                    @showLayout="openLayoutDialog"
                    @copyUnit="copy"
                />
            </template>
            <template #description>
                {{ description }}
            </template>
            <template #footer v-if="certificate">
                <studip-icon shape="medal" :size="32" role="info_alt"></studip-icon>
            </template>
        </courseware-tile>
        <studip-dialog
            v-if="showDeleteDialog"
            :title="$gettext('Lernmaterial löschen')"
            :question="$gettextInterpolate(
                        $gettext('Möchten Sie das Lernmaterial %{ unitTitle } wirklich löschen?'),
                        { unitTitle: title }
                    )"
            height="200"
            @confirm="executeDelete"
            @close="closeDeleteDialog"
        ></studip-dialog>

        <studip-dialog
            v-if="showProgressDialog"
            :title="userIsTeacher ? $gettext('Fortschritt aller Teilnehmenden') : $gettext('Mein Fortschritt')"
            :closeText="$gettext('Schließen')"
            closeClass="cancel"
            width="800"
            height="600"
            @close="closeProgressDialog"
        >
            <template v-slot:dialogContent>
                <courseware-unit-progress :progressData="progresses" :unitId="unit.id" :rootId="parseInt(unitElement.id)"/>
            </template>
        </studip-dialog>

        <courseware-unit-item-dialog-export v-if="showExportDialog" :unit="unit" @close="showExportDialog = false" />
        <courseware-unit-item-dialog-settings v-if="showSettingsDialog" :unit="unit" @close="closeSettingsDialog"/>
        <courseware-unit-item-dialog-layout v-if="showLayoutDialog" :unit="unit" :unitElement="unitElement" @close="closeLayoutDialog"/>
    </li>
</template>

<script>
import CoursewareTile from '../layouts/CoursewareTile.vue';
import CoursewareUnitItemDialogExport from './CoursewareUnitItemDialogExport.vue';
import CoursewareUnitItemDialogSettings from './CoursewareUnitItemDialogSettings.vue';
import CoursewareUnitItemDialogLayout from './CoursewareUnitItemDialogLayout.vue';
import CoursewareUnitProgress from './CoursewareUnitProgress.vue';
import axios from 'axios';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-unit-item',
    components: {
        CoursewareTile,
        CoursewareUnitItemDialogExport,
        CoursewareUnitItemDialogLayout,
        CoursewareUnitItemDialogSettings,
        CoursewareUnitProgress,
    },
    props: {
        unit: Object,
        handle: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            showDeleteDialog: false,
            showExportDialog: false,
            showSettingsDialog: false,
            showProgressDialog: false,
            showLayoutDialog: false,
            progresses: null,
            certificate: null
        }
    },
    computed: {
        ...mapGetters({
            context: 'context',
            structuralElementById: 'courseware-structural-elements/byId',
            userIsTeacher: 'userIsTeacher'
        }),
        menuItems() {
            let menu = [];
            if (this.inCourseContext) {
                menu.push({ id: 1, label: this.$gettext('Fortschritt'), icon: 'progress', emit: 'showProgress' });
                if (this.certificate) {
                    menu.push({
                        id: 2,
                        label: this.$gettext('Zertifikat'),
                        icon: 'medal',
                        url: STUDIP.URLHelper.getURL('sendfile.php', {
                            type: 0,
                            file_id: this.certificate,
                            file_name: this.$gettext('Zertifikat') + '.pdf'
                        })
                    });
                }
            }
            if(this.userIsTeacher && this.inCourseContext) {
                menu.push({ id: 2, label: this.$gettext('Einstellungen'), icon: 'settings', emit: 'showSettings' });
            }
            if(this.userIsTeacher || !this.inCourseContext) {
                menu.push({ id: 4, label: this.$gettext('Darstellung'), icon: 'colorpicker', emit: 'showLayout' });
                menu.push({ id: 4, label: this.$gettext('Duplizieren'), icon: 'copy', emit: 'copyUnit' });
                menu.push({ id: 5, label: this.$gettext('Exportieren'), icon: 'export', emit: 'showExport' });
                menu.push({ id: 6, label: this.$gettext('Löschen'), icon: 'trash', emit: 'showDelete' });
            }

            return menu;
        },
        unitElement() {
            return this.structuralElementById({id: this.unit.relationships['structural-element'].data.id}) ?? null;
        },
        color() {
            return this.unitElement?.attributes?.payload?.color ?? 'studip-blue';
        },
        title() {
            return  this.unitElement?.attributes?.title ?? '';
        },
        description() {
            return  this.unitElement?.attributes?.payload?.description ?? '';
        },
        imageUrl() {
            return this.unitElement?.relationships?.image?.meta?.['download-url'] ?? '';
        },
        url() {
            if (this.inCourseContext) {
                return STUDIP.URLHelper.getURL('dispatch.php/course/courseware/courseware/' + this.unit.id , { cid: this.context.id });
            } else {
                return STUDIP.URLHelper.getURL('dispatch.php/contents/courseware/courseware/' + this.unit.id);
            }
        },
        progress() {
            if (this.unitElement) {
                return this.progresses?.[this.unitElement.id]?.progress?.cumulative ?? 0;
            }
            return 0;
        },
        inCourseContext() {
            return this.context.type === 'courses';
        }
    },
    async mounted() {
        if (this.inCourseContext) {
            this.progresses = await this.loadUnitProgresses({unitId: this.unit.id});
            this.checkCertificate();
        }
    },
    methods: {
        ...mapActions({
            deleteUnit: 'deleteUnit',
            loadUnitProgresses: 'loadUnitProgresses',
            copyUnit: 'copyUnit',
            companionSuccess: 'companionSuccess'
        }),
        async checkCertificate() {
            if (this.getStudipConfig('COURSEWARE_CERTIFICATES_ENABLE')) {
                const response = await axios.get(STUDIP.URLHelper.getURL('jsonapi.php/v1/courseware-units/' +
                    this.unit.id + '/certificate/' + STUDIP.USER_ID));
                if (response.status === 200) {
                    this.certificate = response.data;
                }
            }
        },
        executeDelete() {
            this.deleteUnit({id: this.unit.id});
        },
        openDeleteDialog() {
            this.showDeleteDialog = true;
        },
        closeDeleteDialog() {
            this.showDeleteDialog = false;
        },
        openExportDialog() {
            this.showExportDialog = true;
        },
        async openProgressDialog() {
            this.showProgressDialog = true;
            this.progresses = await this.loadUnitProgresses({unitId: this.unit.id});
        },
        closeProgressDialog() {
            this.showProgressDialog = false;
        },
        openSettingsDialog() {
            this.showSettingsDialog = true;
        },
        closeSettingsDialog() {
            this.showSettingsDialog = false;
        },
        openLayoutDialog() {
            this.showLayoutDialog = true;
        },
        closeLayoutDialog() {
            this.showLayoutDialog = false;
        },
        async copy() {
            await this.copyUnit({unitId: this.unit.id, modified: null});
            this.companionSuccess({ info: this.$gettext('Lernmaterial kopiert.') });
        },
    }
}
</script>
