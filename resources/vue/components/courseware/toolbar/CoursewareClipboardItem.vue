<template>
    <div class="cw-clipboard-item-wrapper">
        <span class="cw-sortable-handle cw-sortable-handle-clipboard"></span>
        <button class="cw-clipboard-item" :class="['cw-clipboard-item-' + kind]" @click.prevent="insertClipboardItem">
            <header class="sr-only">
                {{ srTitle }}
            </header>
            <header class="cw-clipboard-item-title" aria-hidden="true">
                {{ name }}
            </header>
            <p class="cw-clipboard-item-description" :title="description">
                {{ description }}
            </p>
        </button>
        <div class="cw-clipboard-item-action-menu-wrapper">
            <studip-action-menu
                class="cw-clipboard-item-action-menu"
                :items="menuItems"
                :context="name"
                @insertItemCopy="insertClipboardItemCopy"
                @editItem="showEditItem"
                @deleteItem="deleteItem"
            />
        </div>
        <studip-dialog
            v-if="showEditDialog"
            :title="$gettext('Umbenennen')"
            :confirmText="$gettext('Speichern')"
            confirmClass="accept"
            :closeText="$gettext('Abbrechen')"
            closeClass="cancel"
            height="360"
            width="500"
            @close="closeEditItem"
            @confirm="storeItem"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Titel') }}
                        <input type="text" v-model="currentClipboard.attributes.name" >
                    </label>
                    <label>
                        {{ $gettext('Beschreibung') }}
                        <textarea v-model="currentClipboard.attributes.description"></textarea>
                    </label>
                </form>
            </template>
        </studip-dialog>
    </div>
</template>

<script>
import clipboardMixin from '@/vue/mixins/courseware/clipboard.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-clipboard-item',
    mixins: [clipboardMixin],
    props: {
        clipboard: Object,
    },
    data() {
        return {
            showEditDialog: false,
            currentClipboard: null,
        };
    },
    computed: {
        ...mapGetters({
            currentElement: 'currentElement',
        }),
        name() {
            return this.clipboard.attributes.name;
        },
        description() {
            return this.clipboard.attributes.description;
        },
        isBlock() {
            return this.clipboard.attributes['object-type'] === 'courseware-blocks';
        },
        kind() {
            return this.clipboard.attributes['object-kind'];
        },
        blockId() {
            return this.clipboard.attributes['block-id'];
        },
        blockNotFound() {
            return this.clipboard.relationships.block.data === null;
        },
        containerId() {
            return this.clipboard.attributes['container-id'];
        },
        containerNotFound() {
            return this.clipboard.relationships.container.data === null;
        },
        itemNotFound() {
            if (this.isBlock) {
                return this.blockNotFound;
            }

            return this.containerNotFound;
        },
        menuItems() {
            let menuItems = [];
            if (!this.itemNotFound) {
                menuItems.push({
                    id: 1,
                    label: this.$gettext('Aktuellen Stand einfügen'),
                    icon: 'copy',
                    emit: 'insertItemCopy',
                });
            }
            menuItems.push({ id: 2, label: this.$gettext('Umbenennen'), icon: 'edit', emit: 'editItem' });
            menuItems.push({ id: 3, label: this.$gettext('Löschen'), icon: 'trash', emit: 'deleteItem' });

            menuItems.sort((a, b) => a.id - b.id);
            return menuItems;
        },
        srTitle() {
            return this.isBlock ? 
                this.$gettextInterpolate(this.$gettext(`Block %{name} einfügen`), { name: this.name }) :
                this.$gettextInterpolate(this.$gettext(`Abschnitt %{name} einfügen`), { name: this.name });
        }
    },
    methods: {
        ...mapActions({
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
            companionWarning: 'companionWarning',
            copyContainer: 'copyContainer',
            copyBlock: 'copyBlock',
            clipboardInsertBlock: 'clipboardInsertBlock',
            clipboardInsertContainer: 'clipboardInsertContainer',
            loadStructuralElement: 'loadStructuralElement',
            loadContainer: 'loadContainer',
            deleteClipboard: 'courseware-clipboards/delete',
            updateClipboard: 'courseware-clipboards/update',
            loadClipboard: 'courseware-clipboards/loadById',
        }),

        insertClipboardItem() {
            this.insertItem(this.clipboard);
        },

        insertClipboardItemCopy() {
            this.insertItemCopy(this.clipboard);
        },

        deleteItem() {
            this.deleteClipboard({ id: this.clipboard.id });
        },
        showEditItem() {
            this.showEditDialog = true;
        },
        closeEditItem() {
            this.showEditDialog = false;
        },
        async storeItem() {
            this.closeEditItem();
            await this.updateClipboard(this.currentClipboard);
            this.loadClipboard({ id: this.currentClipboard.id });
        },
    },
    mounted() {
        this.currentClipboard = _.cloneDeep(this.clipboard);
    },
};
</script>
