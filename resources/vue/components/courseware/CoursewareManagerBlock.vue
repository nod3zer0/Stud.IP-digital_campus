<template>
    <div :class="{ 'cw-manager-block-clickable': inserter }" class="cw-manager-block" @click="clickItem">
        <span v-if="inserter">
            <studip-icon shape="arr_2left" role="sort" />
        </span>
        {{ block.attributes.title }}
        <div v-if="sortBlocks" class="cw-manager-block-buttons">
            <button :disabled="!canMoveUp" @click="moveUp" :title="$gettext('Element nach oben verschieben')">
                <studip-icon shape="arr_2up" role="sort" />
            </button>
            <button :disabled="!canMoveDown" @click="moveDown" :title="$gettext('Element nach unten verschieben')">
                <studip-icon shape="arr_2down" role="sort" />
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-manager-block',
    props: {
        block: Object,
        inserter: Boolean,
        sortBlocks: Boolean,
        elementType: String,
        canMoveUp: Boolean,
        canMoveDown: Boolean,
        sectionId: Number
    },
    methods: {
        clickItem() {
            if (this.inserter) {
                this.$emit('insertBlock', {block: this.block, source: this.elementType});
            }
        },
        moveUp() {
            if (this.sortBlocks) {
                this.$emit('moveUp', this.block.id, this.sectionId);
            }
        },
        moveDown() {
            if (this.sortBlocks) {
                this.$emit('moveDown', this.block.id, this.sectionId);
            }
        },
    },
};
</script>
