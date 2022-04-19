<template>
    <div
        class="cw-container cw-container-list"
        :class="['cw-container-colspan-' + colSpan, showEditMode && canEdit ? 'cw-container-active' : '']"
    >
        <div class="cw-container-content">
            <header v-if="showEditMode && canEdit" class="cw-container-header">
                <studip-icon v-if="blockedByAnotherUser" shape="lock-locked" />
                <span>{{ container.attributes.title }} ({{container.attributes.width}})</span>
                <span v-if="blockedByAnotherUser" class="cw-default-container-blocker-warning">
                    | {{ $gettextInterpolate('wird im Moment von %{ userName } bearbeitet', { userName: this.blockingUserName }) }}
                </span>
                <courseware-container-actions
                    :canEdit="canEdit"
                    :container="container"
                    @editContainer="displayEditDialog"
                    @deleteContainer="displayDeleteDialog"
                    @sortBlocks="sortBlocks"
                    @removeLock="displayRemoveLockDialog()"
                />
            </header>
            <div class="cw-block-wrapper" :class="{ 'cw-block-wrapper-active': showEditMode }">
                <slot name="containerContent"></slot>
            </div>

            <studip-dialog
                v-if="showEditDialog"
                :title="textEditTitle"
                :confirmText="textEditConfirm"
                confirmClass="accept"
                :closeText="textEditClose"
                closeClass="cancel"
                @close="closeEdit"
                @confirm="storeContainer"
                height="430"
                width="680"
            >
                <template v-slot:dialogContent>
                    <slot name="containerEditDialog"></slot>
                </template>
            </studip-dialog>

            <studip-dialog
                v-if="showDeleteDialog"
                :title="textDeleteTitle"
                :question="textDeleteAlert"
                height="180"
                width="380"
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
    </div>
</template>

<script>
import CoursewareContainerActions from './CoursewareContainerActions.vue';
import StudipDialog from '../StudipDialog.vue';
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-default-container',
    components: {
        CoursewareContainerActions,
        StudipDialog,
    },
    props: {
        containerClass: String,
        container: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            showDeleteDialog: false,
            showEditDialog: false,
            showRemoveLockDialog: false,
            textEditConfirm: this.$gettext('Speichern'),
            textEditClose: this.$gettext('Schließen'),
            textEditTitle: this.$gettext('Abschnitt bearbeiten'),
            textDeleteTitle: this.$gettext('Abschnitt unwiderruflich löschen'),
            textDeleteAlert: this.$gettext('Möchten Sie diesen Abschnitt wirklich löschen?'),
            textRemoveLockTitle: this.$gettext('Sperre aufheben'),
            textRemoveLockAlert: this.$gettext('Möchten Sie die Sperre dieses Block wirklich aufheben? Der Bearbeitungsstand geht dabei unwiderruflich verloren.'),
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
        }),
        showEditMode() {
            return this.$store.getters.viewMode === 'edit';
        },
        colSpan() {
            return this.container.attributes.payload.colspan ? this.container.attributes.payload.colspan : 'full';
        },
        blocked() {
            return this.container?.relationships?.['edit-blocker']?.data !== null;
        },
        blockerId() {
            return this.blocked ? this.container?.relationships?.['edit-blocker']?.data?.id : null;
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
                    parent: { type: this.container.type, id: this.container.id },
                    relationship: "edit-blocker"
                });
                return user ? user : null;
            }

            return null;
        },
        blockingUserName() {
            return this.blockingUser ? this.blockingUser.attributes['formatted-name'] : '';
        },
    },
    methods: {
        ...mapActions({
            deleteContainer: 'deleteContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            companionInfo: 'companionInfo',
        }),
        async displayEditDialog() {
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });

                return false;
            }
            try {
                await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            } catch(error) {
                if (error.status === 409) {
                    this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });
                } else {
                    console.log(error);
                }

                return false;
            }

            this.showEditDialog = true;
        },
        async closeEdit() {
            this.$emit('closeEdit');
            this.showEditDialog = false;
            await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
        },
        async storeContainer() {
            this.$emit('storeContainer');
            this.showEditDialog = false;
            // await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
        },
        async displayDeleteDialog() {
            await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            this.showDeleteDialog = true;
        },
        async closeDeleteDialog() {
            await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            this.showDeleteDialog = false;
        },
        async executeDelete() {
            await this.deleteContainer({
                containerId: this.container.id,
                structuralElementId: this.container.relationships['structural-element'].data.id,
            });
            if(Object.keys(this.$store.getters.blockAdder).length !== 0 && this.$store.getters.blockAdder.container.id === this.container.id) {
                this.$store.dispatch('coursewareBlockAdder', {});
            }
            this.showDeleteDialog = false;
        },
        async sortBlocks() {
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });

                return false;
            }
            try {
                await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            } catch(error) {
                if (error.status === 409) {
                    this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });
                } else {
                    console.log(error);
                }

                return false;
            }
            this.$emit('sortBlocks');
        },
        displayRemoveLockDialog() {
            this.showRemoveLockDialog = true;
        },
        executeRemoveLock() {
            this.unlockObject({ id: this.container.id , type: 'courseware-containers' });
            this.showRemoveLockDialog = false;
        },
    },
    watch: {
        showEditDialog(state) {
            this.$emit('showEdit', state);
        },
        blockedByThisUser(newState, oldState) {
            if (oldState && !newState) {
                this.showDeleteDialog = false;
                this.showEditDialog = false;
            }
        }
    }
};
</script>
