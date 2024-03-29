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
                    @showFeedbackCreate="openFeedbackCreateDialog"
                    @showFeedback="openFeedbackDialog"
                />
            </template>
            <template #description>
                {{ description }}
            </template>
            <template #footer>
                <template v-if="hasFeedbackElement">
                    <studip-five-stars
                        v-if="hasFeedbackEntries"
                        :amount="feedbackAverage"
                        :size="16"
                        :title="
                            $gettextInterpolate($gettext('Lernmaterial wurde mit %{avg} Sternen bewertet'), {
                                avg: feedbackAverage,
                            })
                        "
                    />
                    <studip-five-stars
                        v-else
                        :amount="5"
                        :size="16"
                        role="inactive"
                        :title="$gettext('Lernmaterial wurde noch nicht bewertet')"
                    />
                </template>
                <template v-if="certificate">
                    <studip-icon shape="medal" :size="16" role="info_alt" />
                </template>
            </template>
        </courseware-tile>
        <studip-dialog
            v-if="showDeleteDialog"
            :title="$gettext('Lernmaterial löschen')"
            :question="
                $gettextInterpolate($gettext('Möchten Sie das Lernmaterial %{ unitTitle } wirklich löschen?'), {
                    unitTitle: title,
                })
            "
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
                <courseware-unit-progress
                    :progressData="progresses"
                    :unitId="unit.id"
                    :rootId="parseInt(unitElement.id)"
                />
            </template>
        </studip-dialog>

        <courseware-unit-item-dialog-export v-if="showExportDialog" :unit="unit" @close="showExportDialog = false" />
        <courseware-unit-item-dialog-settings v-if="showSettingsDialog" :unit="unit" @close="closeSettingsDialog" />
        <courseware-unit-item-dialog-layout
            v-if="showLayoutDialog"
            :unit="unit"
            :unitElement="unitElement"
            @close="closeLayoutDialog"
        />
        <feedback-dialog
            v-if="showFeedbackDialog"
            :feedbackElementId="parseInt(feedbackElementId)"
            :currentUser="currentUser"
            @deleted="loadUnit({ id: unit.id })"
            @close="closeFeedbackDialog"
        />
        <feedback-create-dialog
            v-if="showFeedbackCreateDialog"
            :defaultQuestion="$gettext('Bewerten Sie das Lernmaterial')"
            rangeType="courseware-units"
            :rangeId="unit.id"
            @created="loadUnit({ id: unit.id })"
            @close="closeFeedbackCreateDialog"
        />
    </li>
</template>

<script>
import CoursewareTile from '../layouts/CoursewareTile.vue';
import CoursewareUnitItemDialogExport from './CoursewareUnitItemDialogExport.vue';
import CoursewareUnitItemDialogSettings from './CoursewareUnitItemDialogSettings.vue';
import CoursewareUnitItemDialogLayout from './CoursewareUnitItemDialogLayout.vue';
import CoursewareUnitProgress from './CoursewareUnitProgress.vue';
import FeedbackDialog from '../../feedback/FeedbackDialog.vue';
import FeedbackCreateDialog from '../../feedback/FeedbackCreateDialog.vue';
import StudipFiveStars from '../../feedback/StudipFiveStars.vue';
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
        FeedbackDialog,
        FeedbackCreateDialog,
        StudipFiveStars,
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
            certificate: null,
            showFeedbackDialog: false,
            showFeedbackCreateDialog: false,
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            structuralElementById: 'courseware-structural-elements/byId',
            userIsTeacher: 'userIsTeacher',
            canCreateFeedbackElement: 'canCreateFeedbackElement',
            isFeedbackActivated: 'isFeedbackActivated',
            feedbackElementById: 'feedback-elements/byId',
            currentUser: 'currentUser',
        }),
        menuItems() {
            let menu = [];
            if (this.inCourseContext) {
                menu.push({ id: 1, label: this.$gettext('Fortschritt'), icon: 'progress', emit: 'showProgress' });
                if (this.userIsTeacher) {
                    menu.push({ id: 2, label: this.$gettext('Einstellungen'), icon: 'settings', emit: 'showSettings' });
                }
                if (this.isFeedbackActivated) {
                    if (this.canCreateFeedbackElement && !this.hasFeedbackElement) {
                        menu.push({
                            id: 6,
                            label: this.$gettext('Feedback aktivieren'),
                            icon: 'feedback',
                            emit: 'showFeedbackCreate',
                        });
                    }
                    if (this.hasFeedbackElement) {
                        menu.push({
                            id: 6,
                            label: this.$gettext('Feedback anzeigen'),
                            icon: 'feedback',
                            emit: 'showFeedback',
                        });
                    }
                }
                if (this.certificate) {
                    menu.push({
                        id: 3,
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

            if (this.userIsTeacher || !this.inCourseContext) {
                menu.push({ id: 4, label: this.$gettext('Darstellung'), icon: 'colorpicker', emit: 'showLayout' });
                menu.push({ id: 5, label: this.$gettext('Duplizieren'), icon: 'copy', emit: 'copyUnit' });
                menu.push({ id: 7, label: this.$gettext('Exportieren'), icon: 'export', emit: 'showExport' });
                menu.push({ id: 8, label: this.$gettext('Löschen'), icon: 'trash', emit: 'showDelete' });
            }

            menu.sort((a, b) => {
                return a.id - b.id;
            });
            return menu;
        },
        unitElement() {
            return this.structuralElementById({ id: this.unit.relationships['structural-element'].data.id }) ?? null;
        },
        feedbackElementId() {
            return this.unit.relationships['feedback-element']?.data?.id;
        },
        hasFeedbackElement() {
            return this.feedbackElementId !== undefined;
        },
        hasFeedbackEntries() {
            return this.feedbackElement?.attributes?.['has-entries'] ?? false;
        },
        feedbackAverage() {
            return this.feedbackElement?.attributes?.['average-rating'] ?? 0;
        },
        feedbackElement() {
            return this.feedbackElementById({ id: this.feedbackElementId });
        },
        color() {
            return this.unitElement?.attributes?.payload?.color ?? 'studip-blue';
        },
        title() {
            return this.unitElement?.attributes?.title ?? '';
        },
        description() {
            return this.unitElement?.attributes?.payload?.description ?? '';
        },
        imageUrl() {
            return this.unitElement?.relationships?.image?.meta?.['download-url'] ?? '';
        },
        url() {
            if (this.inCourseContext) {
                return STUDIP.URLHelper.getURL('dispatch.php/course/courseware/courseware/' + this.unit.id, {
                    cid: this.context.id,
                });
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
        },
    },
    async mounted() {
        if (this.inCourseContext) {
            this.progresses = await this.loadUnitProgresses({ unitId: this.unit.id });
            this.checkCertificate();
        }
    },
    methods: {
        ...mapActions({
            deleteUnit: 'deleteUnit',
            loadUnitProgresses: 'loadUnitProgresses',
            loadUnit: 'courseware-units/loadById',
            copyUnit: 'copyUnit',
            companionSuccess: 'companionSuccess',
            createFeedback: 'feedback-elements/create',
            loadFeedbackElement: 'feedback-elements/loadById',
        }),
        checkCertificate() {
            if (this.getStudipConfig('COURSEWARE_CERTIFICATES_ENABLE') && this.unit.attributes.config.certificate) {
                axios.get(STUDIP.URLHelper.getURL('jsonapi.php/v1/courseware-units/' +
                    this.unit.id + '/certificate/' + STUDIP.USER_ID))
                    .then(response => {
                        this.certificate = response.data;
                    })
                    .catch(error => {});
            }
        },
        executeDelete() {
            this.deleteUnit({ id: this.unit.id });
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
            this.progresses = await this.loadUnitProgresses({ unitId: this.unit.id });
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
        openFeedbackCreateDialog() {
            this.showFeedbackCreateDialog = true;
        },
        closeFeedbackCreateDialog() {
            this.showFeedbackCreateDialog = false;
        },
        openFeedbackDialog() {
            if (this.feedbackElementId) {
                this.showFeedbackDialog = true;
            }
        },
        closeFeedbackDialog() {
            this.showFeedbackDialog = false;
            this.loadFeedbackElement({ id: this.feedbackElementId });
        },
        async copy() {
            await this.copyUnit({ unitId: this.unit.id, modified: null });
            this.companionSuccess({ info: this.$gettext('Lernmaterial kopiert.') });
        },
    }
}
</script>
