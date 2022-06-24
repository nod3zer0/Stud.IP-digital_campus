<template>
    <div v-if="block.attributes.visible || canEdit" class="cw-default-block">
        <div class="cw-content-wrapper" :class="[showEditMode ? 'cw-content-wrapper-active' : '']">
            <header v-if="showEditMode" class="cw-block-header">
                <span class="cw-sortable-handle"></span>
                <studip-icon v-if="!block.attributes.visible" shape="visibility-invisible" />
                <studip-icon v-if="blockedByAnotherUser" shape="lock-locked" />
                <span>{{ blockTitle }}</span>
                <span v-if="blockedByAnotherUser" class="cw-default-block-blocker-warning">
                    | {{ $gettextInterpolate('wird im Moment von %{ userName } bearbeitet', { userName: this.blockingUserName }) }}
                </span>
                <span v-if="!block.attributes.visible" class="cw-default-block-invisible-info">
                    | {{ $gettext('unsichtbar für Nutzende ohne Schreibrecht') }}
                </span>
                <courseware-block-actions
                    :block="block"
                    :canEdit="canEdit"
                    :deleteOnly="deleteOnly"
                    @editBlock="displayFeature('Edit')"
                    @showInfo="displayFeature('Info')"
                    @showExportOptions="displayFeature('ExportOptions')"
                    @deleteBlock="displayDeleteDialog()"
                    @removeLock="displayRemoveLockDialog()"
                />
            </header>
            <div v-if="showContent" class="cw-block-content">
                <slot name="content" />
            </div>
            <div v-if="showFeatures" class="cw-block-features cw-block-features-default">
                <courseware-block-export-options
                    v-if="canEdit && showExportOptions"
                    :block="block"
                    @close="displayFeature(false)"
                />
                <courseware-block-edit
                    v-if="canEdit && showEdit"
                    :block="block"
                    @store="$emit('storeEdit')"
                    @close="closeEdit"
                >
                    <template #edit>
                        <slot name="edit" />
                    </template>
                </courseware-block-edit>
                <courseware-block-info v-if="showInfo" :block="block" @close="displayFeature(false)">
                    <template #info>
                        <slot name="info" />
                    </template>
                </courseware-block-info>
            </div>
        </div>
        <div v-if="discussView" class="cw-discuss-wrapper">
            <courseware-block-discussion
                :block="block"
                :canEdit="canEdit"
            />
        </div>
        <studip-dialog
            v-if="showDeleteDialog"
            :title="textDeleteTitle"
            :question="textDeleteAlert"
            height="180"
            width="360"
            @confirm="executeDelete"
            @close="showDeleteDialog = false"
        ></studip-dialog>
        <studip-dialog
            v-if="showRemoveLockDialog"
            :title="textRemoveLockTitle"
            :question="textRemoveLockAlert"
            height="200"
            width="450"
            @confirm="executeRemoveLock"
            @close="showRemoveLockDialog = false"
        ></studip-dialog>
    </div>
</template>

<script>
import CoursewareBlockComments from './CoursewareBlockComments.vue';
import CoursewareBlockEdit from './CoursewareBlockEdit.vue';
import CoursewareBlockExportOptions from './CoursewareBlockExportOptions.vue';
import CoursewareBlockFeedback from './CoursewareBlockFeedback.vue';
import CoursewareBlockInfo from './CoursewareBlockInfo.vue';
import CoursewareBlockActions from './CoursewareBlockActions.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import StudipDialog from '../StudipDialog.vue';
import StudipIcon from '../StudipIcon.vue';
import { blockMixin } from './block-mixin.js';
import { mapActions, mapGetters } from 'vuex';
import CoursewareBlockDiscussion from './CoursewareBlockDiscussion.vue';

export default {
    name: 'courseware-default-block',
    mixins: [blockMixin],
    components: {
        CoursewareBlockComments,
        CoursewareBlockEdit,
        CoursewareBlockExportOptions,
        CoursewareBlockFeedback,
        CoursewareBlockActions,
        CoursewareBlockInfo,
        CoursewareTabs,
        CoursewareTab,
        StudipDialog,
        StudipIcon,
        CoursewareBlockDiscussion,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        deleteOnly: {
            type: Boolean,
            default: false
        },
        isTeacher: Boolean,
        preview: Boolean,
        defaultGrade: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            showFeatures: false,
            showExportOptions: false,
            showEdit: false,
            showInfo: false,
            showContent: true,
            showEditModeShortcut: false,
            showDeleteDialog: false,
            showRemoveLockDialog: false,
            currentComments: [],
            textDeleteTitle: this.$gettext('Block unwiderruflich löschen'),
            textDeleteAlert: this.$gettext('Möchten Sie diesen Block wirklich löschen?'),
            textRemoveLockTitle: this.$gettext('Sperre aufheben'),
            textRemoveLockAlert: this.$gettext('Möchten Sie die Sperre dieses Block wirklich aufheben? Der Bearbeitungsstand geht dabei unwiderruflich verloren.'),
        };
    },
    computed: {
        ...mapGetters({
            blockTypes: 'blockTypes',
            containerById: 'courseware-containers/byId',
            context: 'context',
            userId: 'userId',
            userById: 'users/byId',
            viewMode: 'viewMode',
        }),
        showEditMode() {
            let show = this.viewMode === 'edit' || this.blockedByThisUser;
            if (!show) {
                this.displayFeature(false);
            }
            return show;
        },
        discussView() {
            return this.viewMode === 'discuss';
        },
        blocked() {
            return this.block?.relationships?.['edit-blocker']?.data !== null;
        },
        blockerId() {
            return this.blocked ? this.block?.relationships?.['edit-blocker']?.data?.id : null;
        },
        blockedByThisUser() {
            return this.blocked && this.userId === this.blockerId;
        },
        blockedByAnotherUser() {
            return this.blocked && this.userId !== this.blockerId;
        },
        blockingUser() {
            if (this.blockedByAnotherUser) {
                const user = this.$store.getters["users/related"]({
                    parent: { type: this.block.type, id: this.block.id },
                    relationship: "edit-blocker"
                });
                return user ? user : null;
            }

            return null;
        },
        blockingUserName() {
            return this.blockingUser ? this.blockingUser.attributes['formatted-name'] : '';
        },
        blockTitle() {
            const type = this.block.attributes['block-type'];

            return this.blockTypes.find((blockType) => blockType.type === type)?.title || this.$gettext('Fehler');
        },
        public() {
            return this.context.type === 'public';
        }
    },
    mounted() {
        if (this.blocked) {
            if (this.blockedByThisUser) {
                this.displayFeature('Edit');
            }
            if (this.blockedByAnotherUser) {
                this.loadUserById({ id: this.blockerId });
            }
        }
        if (!this.public && this.userProgress && this.userProgress.attributes.grade === 0 && this.defaultGrade) {
            this.userProgress = 1;
        }
    },
    methods: {
        ...mapActions({
            companionInfo: 'companionInfo',
            deleteBlock: 'deleteBlockInContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            loadContainer: 'loadContainer',
            updateContainer: 'updateContainer',
            loadUserById: 'users/loadById'
        }),
        async displayFeature(element) {
            if (this.showEdit && element === 'Edit') {
                return false;
            }
            this.showFeatures = false;
            this.showExportOptions = false;
            this.showEdit = false;
            this.showInfo = false;
            this.showContent = true;
            if (element) {
                if (element === 'Edit') {
                    await this.loadContainer(this.block.relationships.container.data.id);
                    if (!this.blocked) {
                        try {
                            await this.lockObject({ id: this.block.id, type: 'courseware-blocks' });
                        } catch(error) {
                            if (error.status === 403) {
                                this.companionInfo({ info: this.$gettext('Dieser Block wird bereits bearbeitet.') });
                            } else {
                                console.log(error);
                            }

                            return false;
                        }
                        if (!this.preview) {
                            this.showContent = false;
                        }
                        this['show' + element] = true;
                        this.showFeatures = true;
                    } else {
                        if (this.userId === this.blockerId) {
                            if (!this.preview) {
                                this.showContent = false;
                            }
                            this['show' + element] = true;
                            this.showFeatures = true;
                        } else {
                            this.companionInfo({ info: this.$gettext('Dieser Block wird bereits bearbeitet.') });
                        }
                    }
                } else {
                    this['show' + element] = true;
                    this.showFeatures = true;
                }
            }
        },
        async closeEdit() {
            this.displayFeature(false);
            this.$emit('closeEdit');
            await this.unlockObject({ id: this.block.id, type: 'courseware-blocks' });
            this.loadContainer(this.block.relationships.container.data.id); // to update block editor lock
        },
        async displayDeleteDialog() {
            if (!this.blocked) {
                await this.lockObject({ id: this.block.id, type: 'courseware-blocks' });
                this.showDeleteDialog = true;
            } else {
                if (this.userId === this.blockerId) {
                    this.showDeleteDialog = true;
                } else {
                    this.companionInfo({ info: 'Dieser Block wird bereits bearbeitet.' });
                }
            }
        },
        async executeDelete() {
            const containerId = this.block.relationships.container.data.id;
            await this.loadContainer(containerId);
            let container = this.containerById({id: containerId});
            const structuralElementId = container.relationships['structural-element'].data.id;
            let containerBlocks = container.relationships.blocks.data.map(block => {
                return block.id;
            });
            let sections = container.attributes.payload.sections;

            // lock parent container
            await this.lockObject({ id: containerId, type: 'courseware-containers' }); 
            // update container information
            for (let i = 0; i < sections.length; i++) {
                for (let j = 0; j < sections[i].blocks.length; j++) {
                    let blockId = sections[i].blocks[j];
                    if (!containerBlocks.includes(blockId) || blockId === this.block.id) {
                        sections[i].blocks.splice(j, 1);
                        j--;
                    }
                }
            }
            // update container
            await this.updateContainer({ container, structuralElementId });
            // unlock container
            await this.unlockObject({ id: containerId, type: 'courseware-containers' });
            await this.loadContainer(containerId);
            this.deleteBlock({
                blockId: this.block.id,
                containerId: containerId,
            });
        },
        displayRemoveLockDialog() {
            this.showRemoveLockDialog = true;
        },
        executeRemoveLock() {
            this.unlockObject({ id: this.block.id , type: 'courseware-blocks' });
            this.showRemoveLockDialog = false;
        }
    },
    watch: {
        showEdit(state) {
            this.$emit('showEdit', state);
        }
    }
};
</script>
