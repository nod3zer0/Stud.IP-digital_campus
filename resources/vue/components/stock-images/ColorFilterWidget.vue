<template>
    <SidebarWidget :title="$gettext('Farbe')">
        <template #content>
            <studip-select multiple v-model="selectedColors" :options="selectableColors" @input="onVueSelectInput" label="name">
                <template #open-indicator>
                    <span><studip-icon shape="arr_1down" :size="10" /></span>
                </template>

                <template #option="{ name, hex }">
                    <span class="vs__option-color" :style="{ 'background-color': hex }"></span>
                    <span>{{ name }}</span>
                </template>

                <template #selected-option="{ name, hex }">
                    <span class="vs__option-color" :style="{ 'background-color': hex }" :title="name"></span>
                </template>

                <template #no-options>{{ $gettext('Keine Auswahlmöglichkeiten') }}</template>
            </studip-select>
        </template>
    </SidebarWidget>
</template>
<script>
import { colors as selectableColors } from './colors.js';
import SidebarWidget from '../SidebarWidget.vue';
import { orientations } from './filters.js';

export default {
    model: {
        prop: 'filters',
        event: 'change',
    },
    props: {
        filters: {
            type: Object,
            required: true,
        },
    },
    components: {
        SidebarWidget,
    },
    data: () => ({
        selectedColors: [],
    }),
    computed: {
        selectableColors: () => selectableColors,
    },
    methods: {
        onVueSelectInput(selectedColors) {
            const colors = selectedColors.map(({ hex }) => hex);
            this.$emit('change', { ...this.filters, colors });
        },
    },
    mounted() {
        this.selectedColors = this.selectableColors.filter(({ hex }) => this.filters.colors.includes(hex));
    },
    watch: {
        filters: {
            handler(newValue) {
                this.selectedColors = this.selectableColors.filter(({ hex }) => this.filters.colors.includes(hex));
            },
            deep: true,
        },
    },
};
</script>

<!-- <style scoped>
.stock-images-filters-color-swatch {
    box-shadow: 0 0 0 1px var(--base-color-20);
    box-sizing: border-box;
    display: inline-block;
    width: 20px;
    height: 20px;
    transition: all 0.1s;
}
</style> -->
