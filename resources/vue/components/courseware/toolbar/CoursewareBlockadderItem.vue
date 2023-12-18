<template>
    <div class="cw-blockadder-item-wrapper">
        <span class="cw-sortable-handle cw-sortable-handle-blockadder"></span>
        <a href="#" class="cw-blockadder-item" :class="['cw-blockadder-item-' + type]" @click.prevent="addBlock">
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
import containerMixin from '@/vue/mixins/courseware/container.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-blockadder-item',
    mixins: [containerMixin],
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
            containerById: 'courseware-containers/byId',
            favoriteBlockTypes: 'favoriteBlockTypes',
            lastCreatedBlock: 'courseware-blocks/lastCreated',
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
            setAdderStorage: 'coursewareBlockAdder',
        }),
        async addBlock() {
            this.setAdderStorage({ 
                container: this.blockAdder.container, 
                section: this.blockAdder.section, 
                type: this.type ,
                position: false
            });
            this.addNewBlock();
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
