<template>
    <sidebar-widget :title="$gettext('Neueste Einträge')">
        <template #content>
            <div class="cw-filter-widget">
                <form class="default" @submit.prevent="">
                    <select v-model="createdFilter" :aria-label="$gettext('Filter: Neueste Einträge')">
                        <option value="all">
                            {{ $gettext('unbeschränkt') }}
                        </option>
                        <option value="oneDay">
                            {{ $gettext('einen Tag alt') }}
                        </option>
                        <option value="oneWeek">
                            {{ $gettext('eine Woche alt') }}
                        </option>
                    </select>
                </form>
            </div>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-comments-overview-widget-filter-created',
    components: {
        SidebarWidget
    },
    data() {
        return {
            createdFilter: 'all',
        };
    },
    methods: {
        ...mapActions({
            setCreatedFilter: 'setCreatedFilter',
        }),
        filterCreated() {
            this.setCreatedFilter(this.createdFilter);
        },
    },
    watch: {
        createdFilter() {
            this.filterCreated();
        },
    }
};
</script>
