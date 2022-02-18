<template>
    <div class="cw-manager-element-item-wrapper">
        <a
            v-if="!sortChapters"
            href="#"
            class="cw-manager-element-item"
            :class="[inserter ? 'cw-manager-element-item-inserter' : '']"
            :title="inserter ? $gettextInterpolate('%{ elementTitle } verschieben', {elementTitle: element.attributes.title}) : element.attributes.title"
            @click="clickItem">
                {{ element.attributes.title }}
        </a>
        <div 
            v-else
            class="cw-manager-element-item cw-manager-element-item-sorting"
        >
            {{ element.attributes.title }}
            <div v-if="sortChapters" class="cw-manager-element-item-buttons">
                <a v-if="canMoveUp" href="#" @click="moveUp" :title="$gettext('Element nach oben verschieben')">
                    <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveUp}" shape="arr_2up" size="16" role="clickable" />
                </a>
                <a v-if="canMoveDown" href="#" @click="moveDown" :title="$gettext('Element nach unten verschieben')">
                    <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveDown}" shape="arr_2down" size="16" role="clickable" />
                </a>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-manager-element-item',
    props: {
        element: Object,
        inserter: Boolean,
        sortChapters: Boolean,
        type: String,
        canMoveUp: Boolean,
        canMoveDown: Boolean
    },
    methods: {
        clickItem() {
            if (this.sortChapters) {
                return false;
            }
            if (this.inserter) {
                this.$emit('insertElement', {element: this.element, source: this.type});
            } else {
                this.$emit('selectChapter', this.element.id);
            }
        },
        moveUp() {
            if (this.sortChapters) {
                this.$emit('moveUp', this.element.id);
            }
        },
        moveDown() {
            if (this.sortChapters) {
                this.$emit('moveDown', this.element.id);
            }
        },
    },
};
</script>
