<template>
    <div class="cw-blockadder-item-wrapper">
        <a href="#" @click.prevent="addBlock" class="cw-blockadder-item" :class="['cw-blockadder-item-' + type]">
            <header class="cw-blockadder-item-title">
                {{ title }}
            </header>
            <p class="cw-blockadder-item-description">
                {{ description }}
            </p>
        </a>
        <button
            class="cw-blockadder-item-fav"
            :title="favButtonTitle"
            @click="toggleFavItem()"
        >
            <studip-icon :shape="blockTypeIsFav ? 'star' : 'star-empty'" :size="20" />
        </button>
    </div>
    
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-blockadder-item',
    components: {},
    props: {
        title: String,
        description: String,
        type: String,
    },
    data() {
        return {
            showInfo: false,
        };
    },
    computed: {
        ...mapGetters({
            blockAdder: 'blockAdder',
            blockById: 'courseware-blocks/byId',
            lastCreatedBlock: 'courseware-blocks/lastCreated',
            favoriteBlockTypes: 'favoriteBlockTypes',
        }),
        blockTypeIsFav() {
            return this.favoriteBlockTypes.some((type) => type.type === this.type);
        },
        favButtonTitle() {
            if (this.blockTypeIsFav) {
                return this.$gettextInterpolate(
                    this.$gettext('%{ blockName } Block aus den Favoriten entfernen'),
                    { blockName: this.title }
                );
            }

            return this.$gettextInterpolate(
                this.$gettext('%{ blockName } Block zu Favoriten hinzufügen'),
                { blockName: this.title }
            );   
        }
    },
    methods: {
        ...mapActions({
            companionInfo: 'companionInfo',
            companionSuccess: 'companionSuccess',
            companionWarning: 'companionWarning',
            createBlock: 'createBlockInContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            loadBlock: 'courseware-blocks/loadById',
            updateContainer: 'updateContainer',
            removeFavoriteBlockType: 'removeFavoriteBlockType',
            addFavoriteBlockType: 'addFavoriteBlockType',
        }),
        async addBlock() {
            if (Object.keys(this.blockAdder).length !== 0) {
                // lock parent container
                await this.lockObject({ id: this.blockAdder.container.id, type: 'courseware-containers' });
                // create new block
                await this.createBlock({
                    container: this.blockAdder.container,
                    section: this.blockAdder.section,
                    blockType: this.type,
                });
                //get new Block
                const newBlock = this.lastCreatedBlock;
                // update container information -> new block id in sections
                let container = this.blockAdder.container;
                container.attributes.payload.sections[this.blockAdder.section].blocks.push(newBlock.id);
                const structuralElementId = container.relationships['structural-element'].data.id;
                // update container
                await this.updateContainer({ container, structuralElementId });
                // unlock container
                await this.unlockObject({ id: this.blockAdder.container.id, type: 'courseware-containers' });
                this.companionSuccess({
                    info: this.$gettext('Der Block wurde erfolgreich eingefügt.'),
                });
                this.$emit('blockAdded');
            } else {
                // companion action
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie einen Ort aus, an dem der Block eingefügt werden soll.'),
                });
            }
        },
        toggleFavItem() {
            if (this.blockTypeIsFav) {
                this.removeFavoriteBlockType(this.type);
            } else {
                this.addFavoriteBlockType(this.type);
            }
        },
    },
};
</script>
