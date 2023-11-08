<template>
    <sidebar-widget :title="$gettext('Listen')">
        <template #content>
            <div class="cw-filter-widget">
                <form class="default" @submit.prevent="">
                    <select v-model="typeFilter" :aria-label="$gettext('Filter: Listen')">
                        <option value="all">
                            {{ $gettext('Blöcke und Seiten') }}
                        </option>
                        <option value="blocks">
                            {{ $gettext('Blöcke') }}
                        </option>
                        <option value="elements">
                            {{ $gettext('Seiten') }}
                        </option>
                    </select>
                </form>
            </div>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../../SidebarWidget.vue';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-comments-overview-widget-filter-type',
    components: {
        SidebarWidget
    },
    data() {
        return {
            typeFilter: 'all',
        };
    },
    methods: {
        ...mapActions({
            setTypeFilter: 'setTypeFilter',
        }),
        filterType() {
            this.setTypeFilter(this.typeFilter);
        },
    },
    watch: {
        typeFilter() {
            this.filterType();
        },
    }
};
</script>
