<template>
    <sidebar-widget :title="$gettext('Aktivitäten')">
        <template #content>
            <div class="cw-filter-widget">
                <form class="default" @submit.prevent="">
                    <select v-model="typeFilter">
                        <option value="all">
                            {{ $gettext('Alle') }}
                        </option>
                        <option value="edited">
                            {{ $gettext('Bearbeitet') }}
                        </option>
                        <option value="created">
                            {{ $gettext('Erstellt') }}
                        </option>
                        <option value="answered">
                            {{ $gettext('Feedback') }}
                        </option>
                        <option value="interacted">
                            {{ $gettext('Kommentiert') }}
                        </option>
                        <option value="voided">
                            {{ $gettext('Gelöscht') }}
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
    name: 'courseware-activities-widget-filter-type',
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
}
</script>
