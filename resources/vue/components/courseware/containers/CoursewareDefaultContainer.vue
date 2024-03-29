<template>
    <div
        class="cw-container"
        :class="['cw-container-colspan-' + colSpan, showEditMode && canEdit ? 'cw-container-active' : '', containerClass]"
    >
        <div class="cw-container-content">
            <header v-if="showEditMode" class="cw-container-header" :class="{ 'cw-container-header-open': isOpen }">
                <a href="#" class="cw-container-header-toggle" :aria-expanded="isOpen" @click.prevent="isOpen = !isOpen">
                    <studip-icon :shape="isOpen ? 'arr_1down' : 'arr_1right'" />
                    <span>{{ container.attributes.title }} ({{container.attributes.width}})</span>
                    <studip-icon v-if="blockedByAnotherUser" shape="lock-locked" />
                    <span v-if="blockedByAnotherUser" class="cw-default-container-blocker-warning">
                        {{ $gettextInterpolate($gettext('Wird im Moment von %{ userName } bearbeitet'), { userName: this.blockingUserName }) }}
                    </span>
                </a>
                <courseware-container-actions
                    :canEdit="canEdit"
                    :container="container"
                    @editContainer="displayEditDialog"
                    @changeContainer="displayChangeDialog"
                    @deleteContainer="displayDeleteDialog"
                    @removeLock="displayRemoveLockDialog"
                    @copyToClipboard="copyToClipboard"
                />
            </header>
            <div v-show="isOpen"
                class="cw-block-wrapper"
                :class="{
                    'cw-block-wrapper-active': showEditMode,
                }"
            >
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
                :confirmDisabled="!editDataValid"
                height="430"
                width="680"
            >
                <template v-slot:dialogContent>
                    <slot name="containerEditDialog"></slot>
                </template>
                <template v-slot:dialogButtons>
                    <slot name="containerEditButtons"></slot>
                </template>

            </studip-dialog>

            <studip-dialog
                v-if="showChangeDialog"
                :title="$gettext('Abschnitt verändern')"
                :confirmText="$gettext('Speichern')"
                confirmClass="accept"
                :closeText="$gettext('Abbrechen')"
                closeClass="cancel"
                @close="closeChange"
                @confirm="storeChange"
                height="520"
                width="480"
            >
                <template v-slot:dialogContent>
                    <form class="default" @submit.prevent="">
                        <div class="cw-radioset-wrapper" role="group" aria-labelledby="container-type">
                            <p id="container-type">{{ $gettext('Typ') }}</p>
                            <div class="cw-radioset">
                                <div
                                    v-for="(container, index) in containerTypes"
                                    :key="index"
                                    class="cw-radioset-box"
                                    :class="[container.type === changeType ? 'selected' : '']"
                                >
                                    <input type="radio" :id="'type-' + container.type" :value="container.type" v-model="changeType" name="container-type"/>
                                    <label :for="'type-' + container.type" >
                                        <div class="label-icon" :class="[container.type, container.type === changeType ? 'selected' : '']"></div>
                                        <p>{{ container.title }}</p>
                                    </label>

                                </div>
                            </div>
                        </div>
                        <div class="cw-radioset-wrapper" role="group" aria-labelledby="container-style">
                            <p id="container-style">{{ $gettext('Stil') }}</p>
                            <div class="cw-radioset">
                                <div
                                v-for="(style, index) in containerStyles"
                                :key="index"
                                class="cw-radioset-box"
                                :class="[style.colspan === changeStyle ? 'selected' : '']"
                                >
                                    <input type="radio" :id="'change-style-' + style.colspan" :value="style.colspan" v-model="changeStyle" name="change-container-style"/>
                                    <label :for="'change-style-' + style.colspan">
                                        <div class="label-icon" :class="[style.colspan, style.colspan === changeStyle ? 'selected' : '']"></div>
                                        <p>{{ style.title }}</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
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
import StudipDialog from '../../StudipDialog.vue';
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
        editDataValid: {
            type: Boolean,
            default: true
        },
    },
    data() {
        return {
            showDeleteDialog: false,
            showEditDialog: false,
            showChangeDialog: false,
            showRemoveLockDialog: false,
            textEditConfirm: this.$gettext('Speichern'),
            textEditClose: this.$gettext('Schließen'),
            textEditTitle: this.$gettext('Abschnitt bearbeiten'),
            textDeleteTitle: this.$gettext('Abschnitt unwiderruflich löschen'),
            textDeleteAlert: this.$gettext('Möchten Sie diesen Abschnitt wirklich löschen?'),
            textRemoveLockTitle: this.$gettext('Sperre aufheben'),
            textRemoveLockAlert: this.$gettext('Möchten Sie die Sperre dieses Abschnitts wirklich aufheben?'),
            isOpen: true,

            changeType: '',
            changeStyle: '',
        };
    },
    computed: {
        ...mapGetters({
            blockAdder: 'blockAdder',
            userId: 'userId',
            userById: 'users/byId',
            viewMode: 'viewMode',
            currentElementisLink: 'currentElementisLink',
            containerTypes: 'containerTypes',
        }),
        showEditMode() {
            return this.canEdit && !this.currentElementisLink;
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
                return this.userById({id: this.blockerId});
            }

            return null;
        },
        blockingUserName() {
            return this.blockingUser ? this.blockingUser.attributes['formatted-name'] : '';
        },
        containerStyles() {
            return [
                { title: this.$gettext('Volle Breite'), colspan: 'full'},
                { title: this.$gettext('Halbe Breite'), colspan: 'half' },
                { title: this.$gettext('Halbe Breite (zentriert)'), colspan: 'half-center' },
            ];
        },
        type() {
            return this.container.attributes['container-type'];
        }
    },
    methods: {
        ...mapActions({
            companionInfo: 'companionInfo',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
            updateContainer: 'updateContainer',
            loadContainer: 'courseware-containers/loadById',
            deleteContainer: 'deleteContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            coursewareBlockAdder: 'coursewareBlockAdder',
            createClipboard: 'courseware-clipboards/create'
        }),
        async displayEditDialog() {
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });

                return false;
            }

            await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            this.showEditDialog = true;
        },
        async displayChangeDialog() {
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
            if (this.blockedByAnotherUser) {
                this.companionInfo({ info: this.$gettext('Dieser Abschnitt wird bereits bearbeitet.') });

                return false;
            }

            await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            this.changeType = this.type;
            this.changeStyle = this.colSpan;
            this.showChangeDialog = true;
        },
        async storeChange() {
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
            this.closeChange();
            if (this.blockedByAnotherUser) {
                this.companionWarning({
                    info: this.$gettextInterpolate(
                        this.$gettext('Ihre Änderungen konnten nicht gespeichert werden, da %{blockingUserName} die Bearbeitung übernommen hat.'),
                        {blockingUserName: this.blockingUserName}
                    )
                });
                return;
            }
            if (this.blockerId === null) {
                await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
            }

            let container = this.container;
            container.attributes['container-type'] = this.changeType;
            container.attributes.payload.colspan = this.changeStyle;
            await this.updateContainer({
                container: container,
                structuralElementId: this.container.relationships['structural-element'].data.id,
            });
            await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            await this.loadContainer({id : this.container.id });
        },
        async closeChange() {
            await this.loadContainer({ id: this.container.id });
            this.showChangeDialog = false;
            if (this.blockedByThisUser) {
                await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            }
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
        },
        async closeEdit() {
            await this.loadContainer({ id: this.container.id });
            this.$emit('closeEdit');
            this.showEditDialog = false;
            if (this.blockedByThisUser) {
                await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            }
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
        },
        async storeContainer() {
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
            if (this.blockedByThisUser) {
                this.$emit('storeContainer');
            }
            if (this.blockedByAnotherUser) {
                this.companionWarning({
                    info: this.$gettextInterpolate(
                        this.$gettext('Ihre Änderungen konnten nicht gespeichert werden, da %{blockingUserName} die Bearbeitung übernommen hat.'),
                        {blockingUserName: this.blockingUserName}
                    )
                });
                this.$emit('closeEdit');
            }
            if (this.blockerId === null) {
                await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
                this.$emit('storeContainer');
            }
            this.showEditDialog = false;
        },
        async displayDeleteDialog() {
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
            if (!this.blocked) {
                await this.lockObject({ id: this.container.id, type: 'courseware-containers' });
                this.showDeleteDialog = true;
            } else {
                if (this.blockedByThisUser) {
                    this.showDeleteDialog = true;
                } else {
                    this.companionInfo({
                        info: this.$gettextInterpolate(
                            this.$gettext('Löschen nicht möglich, da %{blockingUserName} den Abschnitt bearbeitet.'),
                            {blockingUserName: this.blockingUserName}
                        )
                    });
                }
            }
        },
        async closeDeleteDialog() {
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
            if (this.blockedByThisUser) {
                await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            }
            this.showDeleteDialog = false;
        },
        async executeDelete() {
            await this.loadContainer({ id: this.container.id, options: { include: 'edit-blocker' } });
            if (this.blockedByAnotherUser) {
                this.companionInfo({
                    info: this.$gettextInterpolate(
                        this.$gettext('Löschen nicht möglich, da %{blockingUserName} die Bearbeitung übernommen hat.'),
                        {blockingUserName: this.blockingUserName}
                    )
                });
                return false;
            }
            await this.deleteContainer({
                containerId: this.container.id,
                structuralElementId: this.container.relationships['structural-element'].data.id,
            });
            if(Object.keys(this.blockAdder).length !== 0 && this.blockAdder.container.id === this.container.id) {
                this.coursewareBlockAdder({});
            }
            this.showDeleteDialog = false;
        },
        displayRemoveLockDialog() {
            this.showRemoveLockDialog = true;
        },
        async executeRemoveLock() {
            await this.unlockObject({ id: this.container.id , type: 'courseware-containers' });
            await this.loadContainer({ id: this.container.id });
            this.showRemoveLockDialog = false;
        },

        async copyToClipboard() {
            const clipboard = {
                attributes: {
                    name: this.container.attributes.title,
                    'container-id': this.container.id,
                    'object-type': this.container.type,
                    'object-kind': this.container.attributes['container-type'],

                }
            };

            await this.createClipboard(clipboard, { root: true });
            this.companionSuccess({ info: this.$gettext('Abschnitt wurde in Merkliste abgelegt.') });
        }

    },

    watch: {
        showEditDialog(state) {
            this.$emit('showEdit', state);
        }
    }

};
</script>
