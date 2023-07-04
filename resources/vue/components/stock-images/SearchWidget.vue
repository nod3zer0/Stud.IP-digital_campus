<template>
    <SidebarWidget :title="$gettext('Suche')">
        <template #content>
            <form class="sidebar-search" @submit.prevent="onSearch">
                <ul class="needles">
                    <li>
                        <div class="input-group files-search">
                            <input
                                id="stock-images-search-widget-search"
                                type="text"
                                v-model="searchTerm"
                                :aria-label="$gettext('Geben Sie einen Suchbegriff mit mindestens 3 Zeichen ein.')"
                            />
                            <button
                                v-if="showSearchResults"
                                @click.prevent="onReset"
                                class="reset-search"
                                :title="$gettext('Suchformular zurücksetzen')"
                            >
                                <studip-icon shape="decline" :size="20" role="presentation" alt="" />
                            </button>
                            <button
                                type="submit"
                                :value="$gettext('Suchen')"
                                aria-controls="stock-images-search-widget-search"
                                class="submit-search"
                                :title="$gettext('Suche starten')"
                            >
                                <studip-icon shape="search" :size="20" role="presentation" alt="" />
                            </button>
                        </div>
                    </li>
                </ul>
            </form>
        </template>
    </SidebarWidget>
</template>
<script>
import SidebarWidget from '../SidebarWidget.vue';

export default {
    props: {
        query: {
            type: String,
            default: '',
        },
    },
    components: {
        SidebarWidget,
    },
    data: () => ({
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
