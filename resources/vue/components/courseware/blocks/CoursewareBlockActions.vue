<template>
    <div class="cw-block-actions">
        <studip-action-menu
            :items="menuItems"
            :context="block.attributes.title"
            @editBlock="editBlock"
            @setVisibility="setVisibility"
            @showInfo="showInfo"
            @deleteBlock="deleteBlock"
            @removeLock="removeLock"
            @copyToClipboard="copyToClipboard"
            @deactivateComments="deactivateComments"
            @activateComments="activateComments"
            @showFeedback="showFeedback"
        />
    </div>
</template>

<script>
import StudipActionMenu from '../../StudipActionMenu.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-block-actions',
    components: {
        StudipActionMenu,
    },
    props: {
        canEdit: Boolean,
        deleteOnly: {
            type: Boolean,
            default: false
        },
        block: Object,
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            userIsTeacher: 'userIsTeacher',
            getRelatedFeedback: 'courseware-block-feedback/related',
        }),
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
        hasFeedback() {
            const { id, type } = this.block;
            const feedback = this.getRelatedFeedback({ parent: { id, type }, relationship: 'feedback' });

            if (feedback === null || feedback.length === 0) {
                return false;
            }

            return true;
        },
        menuItems() {
            let menuItems = [];
            if (this.canEdit) {
                if (!this.deleteOnly) {
                    if (!this.blocked) {
                        menuItems.push({ id: 1, label: this.$gettext('Block bearbeiten'), icon: 'edit', emit: 'editBlock' });
                        menuItems.push({
                            id: 3,
                            label: this.block.attributes.visible
                                ? this.$gettext('unsichtbar setzen')
                                : this.$gettext('sichtbar setzen'),
                            icon: this.block.attributes.visible ? 'visibility-visible' : 'visibility-invisible', // do we change the icons ?
                            emit: 'setVisibility',
                        });
                        if (this.userIsTeacher) {
                            menuItems.push({
                                id: 4,
                                label: this.block.attributes.commentable
                                    ? this.$gettext('Kommentare abschalten')
                                    : this.$gettext('Kommentare aktivieren'),
                                icon: 'comment2',
                                emit: this.block.attributes.commentable ? 'deactivateComments' : 'activateComments',
                            });
                        }
                    }
                    if (this.blocked && this.blockedByAnotherUser && this.userIsTeacher) {
                        menuItems.push({
                            id: 8,
                            label: this.$gettext('Sperre aufheben'),
                            icon: 'lock-unlocked',
                            emit: 'removeLock',
                        });
                    }
                    if (!this.blocked || this.blockedByThisUser) {
                        menuItems.push({
                            id: 9,
                            label: this.$gettext('Block löschen'), 
                            icon: 'trash',
                            emit: 'deleteBlock' 
                        });
                    }
                    menuItems.push({
                        id: 2,
                        label: this.$gettext('Block merken'),
                        icon: 'clipboard',
                        emit: 'copyToClipboard'
                    });
                    menuItems.push({
                        id: 7,
                        label: this.$gettext('Informationen zum Block'),
                        icon: 'info',
                        emit: 'showInfo',
                    });
                }
            }
            menuItems.sort((a, b) => {
                return a.id > b.id ? 1 : b.id > a.id ? -1 : 0;
            });

            return menuItems;
        }

    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
        }),
        menuAction(action) {
            this[action]();
        },
        editBlock() {
            this.$emit('editBlock');
        },
        showInfo() {
            this.$emit('showInfo');
        },
        showExportOptions() {
            this.$emit('showExportOptions');
        },
        async setVisibility() {
            if (!this.blocked) {
                await this.lockObject({ id: this.block.id, type: 'courseware-blocks' });
            } else {
                if (this.blockerId !== this.userId) {
                    return false;
                }
            }
            let attributes = {};
            attributes.visible = !this.block.attributes.visible;

            await this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });

            await this.unlockObject({ id: this.block.id, type: 'courseware-blocks' });
        },
        deleteBlock() {
            this.$emit('deleteBlock');
        },
        removeLock() {
            this.$emit('removeLock');
        },
        copyToClipboard() {
            this.$emit('copyToClipboard');
        },
        activateComments() {
            this.$emit('activateComments')
        },
        deactivateComments() {
            this.$emit('deactivateComments')
        },
        showFeedback() {
            this.$emit('showFeedback');
        }
    },
};
</script>
