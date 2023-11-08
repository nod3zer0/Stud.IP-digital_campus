<template>
    <div class="cw-clipboard-item-wrapper">
        <button class="cw-clipboard-item" :class="['cw-clipboard-item-' + kind]" @click.prevent="insertItem">
            <header class="sr-only">
                {{ srTitle }}
            </header>
            <header class="cw-clipboard-item-title" aria-hidden="true">
                {{ name }}
            </header>
            <p class="cw-clipboard-item-description">
                {{ description }}
            </p>
        </button>
        <div class="cw-clipboard-item-action-menu-wrapper">
            <studip-action-menu
                class="cw-clipboard-item-action-menu"
                :items="menuItems"
                :context="name"
                @insertItemCopy="insertItemCopy"
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
                        <input type="text" v-model="currentClipboard.attributes.name" />
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
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-clipboard-item',
    components: {},
    props: {
        clipboard: Object,
    },
    data() {
        return {
            showEditDialog: false,
            currentClipboard: null,

            text: {
                errorMessage: this.$gettext('Es ist ein Fehler aufgetreten.'),
                positionWarning: this.$gettext(
                    'Bitte wählen Sie einen Ort aus, an dem der Block eingefügt werden soll.'
                ),
                blockSuccess: this.$gettext('Der Block wurde erfolgreich eingefügt.'),
                containerSuccess: this.$gettext('Der Abschnitt wurde erfolgreich eingefügt.'),
            },
        };
    },
    computed: {
        ...mapGetters({
            blockAdder: 'blockAdder',
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
                    label: this.$gettext('Kopie des aktuellen Stands einfügen'),
                    icon: 'copy',
                    emit: 'insertItemCopy',
                });
            }
            menuItems.push({ id: 2, label: this.$gettext('Umbenennen'), icon: 'edit', emit: 'editItem' });
            menuItems.push({ id: 3, label: this.$gettext('Löschen'), icon: 'trash', emit: 'deleteItem' });

            menuItems.sort((a, b) => a.id - b.id);
            return menuItems;
        },
        blockAdderActive() {
            return Object.keys(this.blockAdder).length !== 0;
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

        async insertItem() {
            let insertError = false;

            if (this.isBlock) {
                if (!this.blockAdderActive) {
                    this.companionWarning({ info: this.text.positionWarning });
                    return;
                }
                try {
                    await this.clipboardInsertBlock({
                        parentId: this.blockAdder.container.id,
                        section: this.blockAdder.section,
                        clipboard: this.clipboard,
                    });
                } catch (error) {
                    insertError = true;
                    this.companionWarning({ info: this.text.errorMessage });
                }
                if (!insertError) {
                    await this.loadContainer(this.blockAdder.container.id);
                    this.companionSuccess({ info: this.text.blockSuccess });
                }
            } else {
                try {
                    await this.clipboardInsertContainer({
                        parentId: this.currentElement,
                        clipboard: this.clipboard,
                    });
                } catch (error) {
                    insertError = true;
                    this.companionWarning({ info: this.text.errorMessage });
                }
                if (!insertError) {
                    this.loadStructuralElement(this.currentElement);
                    this.companionSuccess({ info: this.text.containerSuccess });
                }
            }
        },

        async insertItemCopy() {
            let insertError = false;

            if (this.isBlock) {
                if (!this.blockAdderActive) {
                    this.companionWarning({ info: this.text.positionWarning });
                    return;
                }
                try {
                    await this.copyBlock({
                        parentId: this.blockAdder.container.id,
                        section: this.blockAdder.section,
                        block: { id: this.blockId },
                    });
                } catch (error) {
                    insertError = true;
                    this.companionWarning({ info: this.text.errorMessage });
                }
                if (!insertError) {
                    await this.loadContainer(this.blockAdder.container.id);
                    this.companionSuccess({ info: this.text.blockSuccess });
                }
            } else {
                try {
                    await this.copyContainer({ parentId: this.currentElement, container: { id: this.containerId } });
                } catch (error) {
                    insertError = true;
                    this.companionWarning({ info: this.text.errorMessage });
                }
                if (!insertError) {
                    this.loadStructuralElement(this.currentElement);
                    this.companionSuccess({ info: this.text.containerSuccess });
                }
            }
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
