import containerMixin from './container';
import { mapActions, mapGetters } from 'vuex';

const clipboardMixin = {
    mixins: [containerMixin],
    data() {
        return {
            text: {
                errorMessage: this.$gettext('Es ist ein Fehler aufgetreten.'),
                positionWarning: this.$gettext(
                    'Bitte fügen Sie der Seite einen Abschnitt hinzu, damit der Block eingefügt werden kann.'
                ),
                blockSuccess: this.$gettext('Der Block wurde erfolgreich eingefügt.'),
                containerSuccess: this.$gettext('Der Abschnitt wurde erfolgreich eingefügt.'),
            }
        }
    },
    computed: {
        ...mapGetters({
            blockAdder: 'blockAdder',
            currentElement: 'currentElement',
        }),

        blockAdderActive() {
            return Object.keys(this.blockAdder).length !== 0;
        },
    },
    methods: {
        ...mapActions({
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
            companionWarning: 'companionWarning',
            clipboardInsertBlock: 'clipboardInsertBlock',
            clipboardInsertContainer: 'clipboardInsertContainer',
            loadStructuralElement: 'loadStructuralElement',
            loadContainer: 'loadContainer',
            loadClipboard: 'courseware-clipboards/loadById',
        }),

        async insertItem(clipboard, itemPosition) {
            const isBlock = clipboard.attributes['object-type'] === 'courseware-blocks';
            let insertError = false;

            if (isBlock) {
                if (!this.blockAdderActive) {
                    this.companionWarning({ info: this.text.positionWarning });
                    return;
                }
                try {
                    await this.clipboardInsertBlock({
                        parentId: this.blockAdder.container.id,
                        section: this.blockAdder.section,
                        clipboard: clipboard,
                    });
                } catch (error) {
                    insertError = true;
                    this.companionWarning({ info: this.text.errorMessage });
                }
                if (!insertError) {
                    await this.loadContainer(this.blockAdder.container.id);
                    if (this.blockAdder.position !== undefined) {
                        await this.sortClipboardBlock();
                    }
                    this.companionSuccess({ info: this.text.blockSuccess });
                }
            } else {
                try {
                    await this.clipboardInsertContainer({
                        parentId: this.currentElement,
                        clipboard: clipboard,
                    });

                } catch (error) {
                    insertError = true;
                    this.companionWarning({ info: this.text.errorMessage });
                }
                if (!insertError) {
                    await this.loadStructuralElement(this.currentElement);
                    itemPosition = itemPosition ? itemPosition : 'last';
                    this.sortContainer(itemPosition);
                    this.companionSuccess({ info: this.text.containerSuccess });
                }
            }
        },

        async insertItemCopy(clipboard) {
            const isBlock = clipboard.attributes['object-type'] === 'courseware-blocks';
            let insertError = false;

            if (isBlock) {
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
    }
}

export default clipboardMixin;