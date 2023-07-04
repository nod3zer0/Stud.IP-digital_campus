<template>
    <div>
        <form @submit.prevent="onSearch">
            <slot name="filters"></slot>

            <input
                :id="`search-bar-input-${searchId}`"
                class="search-bar-input"
                type="text"
                v-model="searchTerm"
                :aria-label="$gettext('Geben Sie einen Suchbegriff mit mindestens 3 Zeichen ein.')"
            />

            <button
                v-if="showSearchResults"
                class="search-bar-erase"
                type="button"
                :title="$gettext('Suchformular zurücksetzen')"
                @click="onReset"
            >
                <StudipIcon shape="decline" :size="20" />
            </button>

            <button
                type="button"
                :title="$gettext('Suchfilter einstellen')"
                class="search-bar-filter"
                :class="{ active: showFilterPanel }"
                @click="onToggleFilterPanel"
                :aria-controls="`search-bar-filter-panel-${searchId}`"
                :aria-expanded="showFilterPanel ? 'true' : 'false'"
            >
                <StudipIcon shape="filter" :role="showFilterPanel ? 'info_alt' : 'clickable'" :size="20" alt="" />
            </button>

            <button
                type="submit"
                :value="$gettext('Suchen')"
                :aria-controls="`search-bar-input-${searchId}`"
                class="submit-search"
                :title="$gettext('Suche starten')"
            >
                <StudipIcon shape="search" :size="20" role="presentation" alt="" />
            </button>
        </form>
        <div :id="`search-bar-filter-panel-${searchId}`" class="filterpanel" ref="filterPanel" v-if="showFilterPanel">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import StudipIcon from './StudipIcon.vue';

let searchIndex = 0;

export default {
    props: {
        query: {
            type: String,
            required: true,
        },
    },
    components: {
        StudipIcon,
    },
    data: () => ({
        searchId: searchIndex++,
        showFilterPanel: false,
        searchTerm: '',
    }),
    computed: {
        showSearchResults() {
            return this.query.length > 0;
        },
    },
    methods: {
        onReset() {
            this.searchTerm = '';
            this.onSearch();
        },
        onSearch() {
            this.$emit('search', this.searchTerm);
        },
        onToggleFilterPanel() {
            this.showFilterPanel = !this.showFilterPanel;
        },
    },
    mounted() {
        this.searchTerm = this.query;
    },
    watch: {
        query(searchTerm) {
            this.searchTerm = searchTerm;
        },
    },
};
</script>

<style scoped>
form {
    align-items: stretch;
    border: thin solid var(--content-color-40);
    display: flex;
    justify-content: space-between;
    width: 100%;
}

input {
    border: none;
    flex-grow: 1;
    padding-inline-start: 0.75em;
    width: 100%;
}

input.search-bar-input {
    line-height: 1.5;
    padding-block: 0.25em;
}

button {
    align-items: center;
    background-color: var(--content-color-20);
    border: none;
    border-inline-start: thin solid var(--content-color-40);
    display: flex;
    justify-content: center;
    width: 2.5em;
}

button.active {
    background-color: var(--base-color);
}

button.search-bar-erase {
    background-color: var(--white);
    border-inline-start: none;
}

.search-bar-filter--remove {
    margin-inline-start: 5px;
}

.filterpanel {
    width: calc(100% + 2px);
    background-color: var(--white);
    border: thin solid var(--content-color-40);
    border-top: none;
    box-sizing: border-box;
    padding: 10px;
}

.filterpanel::before,
.filterpanel::after {
    right: 50px;
}
</style>
