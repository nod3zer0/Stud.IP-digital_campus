<template>
    <div>
        <div>
            <SelectorSearch
                :active-filters="activeFilters"
                :query="query"
                @search="onSearch"
                @update-active-filters="onUpdateActiveFilters"
            />
        </div>
        <ul>
            <li v-for="stockImage in filteredStockImages" :key="stockImage.id">
                <SelectableImageCard :stock-image="stockImage" @click.native="onSelectImage(stockImage)" @keyup.enter.native="onSelectImage(stockImage)" />
            </li>
        </ul>
    </div>
</template>

<script>
import SelectorSearch from './SelectorSearch.vue';
import SelectableImageCard from './SelectableImageCard.vue';
import { searchFilterAndSortImages } from './filters.js';

export default {
    props: {
        stockImages: {
            type: Array,
            required: true,
        },
    },
    data: () => ({
        activeFilters: {
            colors: [],
            orientation: 'landscape',
        },
        query: '',
    }),
    components: { SelectorSearch, SelectableImageCard },
    computed: {
        filteredStockImages() {
            return searchFilterAndSortImages(this.stockImages, this.query, this.activeFilters);
        },
    },
    methods: {
        onUpdateActiveFilters(activeFilters) {
            this.activeFilters = activeFilters;
        },
        onSearch(query) {
            this.query = query;
        },
        onSelectImage(stockImage) {
            this.$emit('select', stockImage);
        },
    },
};
</script>

<style scoped>
ul {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: flex-start;
    align-items: center;
    list-style: none;
    padding: 1rem 0;
}
</style>
