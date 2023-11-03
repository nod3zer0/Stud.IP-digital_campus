<template>
    <sidebar-widget id="search-widget" class="sidebar-search" :title="$gettext('Suche')">
        <template #content>
            <form class="sidebar-search">
                <ul class="needles">
                    <li>
                        <div class="input-group files-search">
                            <input type="text" id="searchterm" name="searchterm" v-model.trim="searchterm"
                                   :placeholder="$gettext('Veranstaltung suchen')"
                                   :aria-label="$gettext('Veranstaltung suchen')">
                            <a v-if="isActive" @click.prevent="cancelSearch" class="reset-search">
                                <studip-icon shape="decline" :size="20"></studip-icon>
                            </a>
                            <button type="submit" class="submit-search" :title="$gettext('Suchen')"
                                    @click.prevent="doSearch">
                                <studip-icon shape="search"
                                             :role="maySearch ? 'clickable' : 'inactive'"
                                             :size="20"
                                ></studip-icon>
                            </button>
                        </div>
                    </li>
                </ul>
            </form>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from './SidebarWidget.vue';
import StudipIcon from './StudipIcon.vue';

export default {
    name: 'search-widget',
    components: {
        StudipIcon,
        SidebarWidget
    },
    props: {
        minLength: {
            type: Number,
            default: 0,
        }
    },
    data() {
        return {
            searchterm: '',
            isActive: false
        };
    },
    computed: {
        maySearch() {
            return this.searchterm.length >= this.minLength;
        }
    },
    methods: {
        doSearch() {
            if (!this.maySearch) {
                return;
            }

            if (this.searchterm !== '') {
                this.isActive = true;
                STUDIP.eventBus.emit('do-search', this.searchterm);
            } else {
                this.cancelSearch();
            }
        },
        cancelSearch() {
            this.isActive = false;
            this.searchterm = '';
            STUDIP.eventBus.emit('cancel-search');
        }
    },
    mounted() {
        const url = new URL(window.location.href);
        if (url.searchParams.has('search')) {
            this.searchterm = url.searchParams.get('search');
            this.doSearch();
        }
    }
}
</script>
