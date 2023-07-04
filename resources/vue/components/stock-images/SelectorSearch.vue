<template>
    <SearchWithFilter :query="query" @search="onSearch">
        <template #filters>
            <ActiveFilter
                v-if="hasOrientationFilter"
                :name="orientations[orientation].text"
                @remove="onRemoveOrientationFilter()"
            >
                {{ orientations[orientation].text }}
            </ActiveFilter>

            <ActiveFilter
                v-for="color in selectedColors"
                :key="color.hex"
                :name="$gettextInterpolate($gettext('Farbe %{color}'), { color: color.name })"
                @remove="onRemoveColorFilter(color)"
            >
                <label>
                    <b class="stock-images-color-patch" :style="`background-color: ${color.hex}`"></b>
                </label>
            </ActiveFilter>
        </template>

        <div class="stock-images-search-filter-panel">
            <div>
                <label>
                    <div>{{ $gettext('Seitenausrichtung') }}</div>

                    <select v-model="orientation">
                        <option v-for="[key, value] in Object.entries(orientations)" :value="key" :key="`orientation-option-${key}`">
                            {{ value.text }}
                        </option>
                    </select>
                </label>
            </div>

            <div>
                <div>{{ $gettext('Farbfilter') }}</div>

                <studip-select
                    multiple
                    v-model="selectedColors"
                    :options="selectableColors"
                    label="name"
                >
                    <template #open-indicator>
                        <span><studip-icon shape="arr_1down" :size="10" /></span>
                    </template>

                    <template #option="{ name, hex }">
                        <span class="vs__option-color" :style="{ 'background-color': hex }"></span>
                        <span>{{ name }}</span>
                    </template>

                    <template #selected-option-container>{{ ' ' }}</template>

                    <template #no-options>{{ $gettext('Keine Auswahlmöglichkeiten') }}</template>
                </studip-select>
            </div>
        </div>
    </SearchWithFilter>
</template>

<script>
import ActiveFilter from '../ActiveFilter.vue';
import SearchWithFilter from '../SearchWithFilter.vue';
import { colors as selectableColors } from './colors.js';
import { orientations, similarColors } from './filters.js';

export default {
    props: {
        activeFilters: {
            type: Object,
            required: true,
        },
        query: {
            type: String,
            required: true,
        },
    },
    components: {
        ActiveFilter,
        SearchWithFilter,
    },
    data: () => ({
        orientation: 'any',
        selectedColors: [],
    }),
    computed: {
        hasOrientationFilter() {
            return this.orientation && this.orientation !== 'any';
        },
        orientations: () => orientations,
        selectableColors: () => selectableColors,
        showSearchResults() {
            return this.query.length > 0;
        },
    },
    methods: {
        onRemoveColorFilter(color) {
            this.selectedColors = this.selectedColors.filter((clr) => clr.hex !== color.hex);
            this.updateActiveFilters();
        },
        onRemoveOrientationFilter() {
            this.orientation = 'any';
        },
        onReset() {
            this.onSearch();
        },
        onSearch(searchTerm = null) {
            this.$emit('search', searchTerm);
        },
        resetLocalFilters() {
            this.selectedColors = this.activeFilters?.colors
                ? this.selectableColors.filter(({ hex }) => this.activeFilters.colors.includes(hex))
                : [];
            this.orientation = this.activeFilters?.orientation ?? 'any';
        },
        updateActiveFilters() {
            const activeFilters = {
                colors: this.selectedColors.map(({ hex }) => hex),
                orientation: this.orientation,
            };
            this.$emit('update-active-filters', activeFilters);
        },
    },
    mounted() {
        this.resetLocalFilters();
    },
    watch: {
        activeFilters() {
            this.resetLocalFilters();
        },
        orientation(newVal, oldVal) {
            this.updateActiveFilters();
        },
    },
};
</script>

<style scoped>
.stock-images-search-filter-panel {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
}
.stock-images-search-filter-panel > * {
    flex-grow: 1;
    flex-basis: calc((30rem - 100%) * 999);
}
.stock-images-search-filter-panel select {
    width: 100%;
    max-width: 48em;
}
b.stock-images-color-patch {
    border: solid thin var(--base-color-20);
    display: inline-block;
    vertical-align: bottom;
    width: 20px;
    height: 20px;
}
</style>
