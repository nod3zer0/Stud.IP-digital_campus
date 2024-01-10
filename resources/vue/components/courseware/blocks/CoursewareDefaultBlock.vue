<template>
    <div v-if="block.attributes.visible || canEdit" class="cw-default-block" :class="[showEditMode ? 'cw-default-block-active' : '']">
        <div class="cw-content-wrapper" :class="[showEditMode ? 'cw-content-wrapper-active' : '']">
            <header v-if="showEditMode" class="cw-block-header">
                <a href="#" class="cw-block-header-toggle" :aria-expanded="isOpen" @click.prevent="isOpen = !isOpen">
                    <studip-icon :shape="isOpen ? 'arr_1down' : 'arr_1right'" />
                    <span>{{ blockTitle }}</span>
                    <studip-icon v-if="blockedByAnotherUser" shape="lock-locked" />
                    <span v-if="blockedByAnotherUser" class="cw-default-block-blocker-warning">
                        {{ $gettextInterpolate($gettext('Wird im Moment von %{ userName } bearbeitet'), { userName: this.blockingUserName }) }}
                    </span>
                    <studip-icon v-if="!block.attributes.visible" shape="visibility-invisible" />
                    <span v-if="!block.attributes.visible" class="cw-default-block-invisible-info">
                        {{ $gettext('Unsichtbar für Nutzende ohne Schreibrecht') }}
                    </span>
                </a>
                <courseware-block-actions
                    :block="block"
                    :canEdit="canEdit"
                    :deleteOnly="deleteOnly"
                    @editBlock="displayFeature('Edit')"
                    @showInfo="displayFeature('Info')"
                    @showExportOptions="displayFeature('ExportOptions')"
                    @deleteBlock="displayDeleteDialog()"
                    @removeLock="displayRemoveLockDialog()"
                    @copyToClipboard="copyToClipboard"
                    @deactivateComments="deactivateComments"
                    @activateComments="activateComments"
                    @showFeedback="showFeedback"
                />
            </header>
            <div v-show="isOpen">
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
                        :preview="preview"
                        @store="prepareStoreEdit"
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
        </div>
        <courseware-block-discussion
            :block="block"
            :canEdit="canEdit"
            :commentable="commentable"
            :displayFeedback="displayFeedback"
        />
        <studip-dialog
            v-if="showDeleteDialog"
            :title="textDeleteTitle"
            :question="textDeleteAlert"
            height="180"
            width="360"
            @confirm="executeDelete"
            @close="closeDeleteDialog"
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
import CoursewareBlockActions from './CoursewareBlockActions.vue';
import CoursewareBlockDiscussion from './CoursewareBlockDiscussion.vue';
import CoursewareBlockEdit from './CoursewareBlockEdit.vue';
import CoursewareBlockExportOptions from './CoursewareBlockExportOptions.vue';
import CoursewareBlockInfo from './CoursewareBlockInfo.vue';
import StudipDialog from '../../StudipDialog.vue';
import StudipIcon from '../../StudipIcon.vue';
import blockMixin from '@/vue/mixins/courseware/block.js';
import { mapActions, mapGetters } from 'vuex';


export default {
    name: 'courseware-default-block',
    mixins: [blockMixin],
    components: {
        CoursewareBlockActions,
        CoursewareBlockDiscussion,
        CoursewareBlockEdit,
        CoursewareBlockExportOptions,
        CoursewareBlockInfo,
        StudipDialog,
        StudipIcon,
        
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
            textRemoveLockAlert: this.$gettext('Möchten Sie die Sperre dieses Blocks wirklich aufheben?'),
            isOpen: true,
            displayFeedback: false,
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
            currentElementisLink: 'currentElementisLink',
        }),
        showEditMode() {
            let show = this.canEdit && !this.currentElementisLink;
            if (!show) {
                this.displayFeature(false);
            }
            return show;
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
                return this.userById({id: this.blockerId});
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
        },
        commentable() {
            return this.block?.attributes?.commentable ?? false;
        },
    },
    mounted() {
        if (this.blocked) {
            if (this.blockedByThisUser) {
                this.displayFeature('Edit');
            }
        }
        if (!this.public && this.userProgress && this.userProgress.attributes.grade === 0 && this.defaultGrade) {
            this.userProgress = 1;
        }
        if (this.canEdit) {
            this.loadFeedback(this.block.id);
        }
    },
    methods: {
        ...mapActions({
            companionInfo: 'companionInfo',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
            deleteBlock: 'deleteBlockInContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            loadContainer: 'loadContainer',
            loadBlock: 'courseware-blocks/loadById',
            updateContainer: 'updateContainer',
            createClipboard: 'courseware-clipboards/create',
            activateBlockComments: 'activateBlockComments',
            deactivateBlockComments: 'deactivateBlockComments',
            loadRelatedFeedback: 'courseware-block-feedback/loadRelated',
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
                    await this.loadBlock({ id: this.block.id, options: { include: 'edit-blocker' } });
                    if (!this.blocked) {
                        await this.lockObject({ id: this.block.id, type: 'courseware-blocks' });
                        if (!this.preview) {
                            this.showContent = false;
                        }
                        this['show' + element] = true;
                        this.showFeatures = true;
                    } else {
                        if (this.blockedByThisUser) {
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
        prepareStoreEdit() {
            // storeEdit is only emitted when the block is not in deleting process.
            if (!this.showDeleteDialog) {
                this.storeBlock();
            }
        },
        async storeBlock() {
            await this.loadBlock({ id: this.block.id, options: { include: 'edit-blocker' } });

            if (this.blockedByThisUser) {
                this.$emit('storeEdit');
            }

            if (this.blockedByAnotherUser) {
                this.companionWarning({
                    info: this.$gettextInterpolate(
                        this.$gettext('Ihre Änderungen konnten nicht gespeichert werden, da %{blockingUserName} die Bearbeitung übernommen hat.'),
                        {blockingUserName: this.blockingUserName}
                    )
                });
                this.displayFeature(false);
                this.$emit('closeEdit');
            }
            if (this.blockerId === null) {
                await this.lockObject({ id: this.block.id, type: 'courseware-blocks' });
                this.$emit('storeEdit');
            }
        },
        async closeEdit() {
            await this.loadBlock({ id: this.block.id , options: { include: 'edit-blocker' } }); // has block editor lock changed?
            this.displayFeature(false);
            this.$emit('closeEdit');
            if (this.blockedByThisUser) {
                await this.unlockObject({ id: this.block.id, type: 'courseware-blocks' });
            }
            this.loadBlock({ id: this.block.id , options: { include: 'edit-blocker' } }); // to update block editor lock
        },
        async displayDeleteDialog() {
            await this.loadBlock({ id: this.block.id, options: { include: 'edit-blocker' } });
            if (!this.blocked) {
                await this.lockObject({ id: this.block.id, type: 'courseware-blocks' });
                this.showDeleteDialog = true;
            } else {
                if (this.blockedByThisUser) {
                    this.showDeleteDialog = true;
                } else {
                    this.companionInfo({
                        info: this.$gettextInterpolate(
                            this.$gettext('Löschen nicht möglich, da %{blockingUserName} den Block bearbeitet.'),
                            {blockingUserName: this.blockingUserName}
                        )
                    });
                }
            }
        },
        async closeDeleteDialog() {
            await this.loadBlock({ id: this.block.id, options: { include: 'edit-blocker' } });
            if (this.blockedByThisUser) {
                await this.unlockObject({ id: this.block.id, type: 'courseware-blocks' });
            }
            this.showDeleteDialog = false;
        },
        async executeDelete() {
            this.showDeleteDialog = false;
            this.displayFeature(false);
            this.$emit('closeEdit');
            await this.loadBlock({ id: this.block.id, options: { include: 'edit-blocker' } });
            if (this.blockedByAnotherUser) {
                this.companionInfo({
                    info: this.$gettextInterpolate(
                        this.$gettext('Löschen nicht möglich, da %{blockingUserName} die Bearbeitung übernommen hat.'),
                        {blockingUserName: this.blockingUserName}
                    )
                });
                return false;
            }
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
        async executeRemoveLock() {
            await this.unlockObject({ id: this.block.id , type: 'courseware-blocks' });
            await this.loadBlock({ id: this.block.id });
            this.showRemoveLockDialog = false;
        },
        async copyToClipboard() {
            const clipboard = {
                attributes: {
                    name: this.block.attributes.title,
                    'block-id': this.block.id,
                    'object-type': this.block.type,
                    'object-kind': this.block.attributes['block-type'],
                }
            };

            await this.createClipboard(clipboard, { root: true });
            this.companionSuccess({ info: this.$gettext('Block wurde in Merkliste abgelegt.') });
        },
        activateComments() {
            this.activateBlockComments({ block: this.block });
        },
        deactivateComments() {
            this.deactivateBlockComments({ block: this.block });
        },
        showFeedback() {
            console.log('displayFeedback');
            this.displayFeedback = true;
        },
        async loadFeedback() {
            const parent = {
                type: this.block.type,
                id: this.block.id,
            };
            await this.loadRelatedFeedback({
                parent,
                relationship: 'feedback',
                options: {
                    include: 'user',
                },
            });
        },

    },
    watch: {
        showEdit(state) {
            this.$emit('showEdit', state);
        }
    }
};
</script>