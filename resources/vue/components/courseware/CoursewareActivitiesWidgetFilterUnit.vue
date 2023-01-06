<template>
    <sidebar-widget :title="$gettext('Lernmaterial')">
        <template #content>
            <div class="cw-filter-widget">
                <form class="default" @submit.prevent="">
                    <select v-model="unitFilter">
                        <option value="all">
                            {{ $gettext('Alle') }}
                        </option>
                        <option v-for="unit in coursewareUnits" :key="unit.id" :value="unit.id">
                                {{ getUnitTitle(unit) }}
                        </option>
                    </select>
                </form>
            </div>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-activities-widget-filter-unit',
    components: {
        SidebarWidget
    },
    data() {
        return {
            unitFilter: 'all'
        };
    },
    computed: {
        ...mapGetters({
            getStructuralElementById: 'courseware-structural-elements/byId',
            coursewareUnits: 'courseware-units/all',
        }),
    },
    methods: {
        ...mapActions({
            setUnitFilter: 'setUnitFilter',
        }),
        filterUnit() {
            this.setUnitFilter(this.unitFilter);
        },
        getUnitTitle(unit) {
            return this.getStructuralElementById({id: unit.relationships['structural-element'].data.id }).attributes.title;
        }
    },
    watch: {
        unitFilter() {
            this.filterUnit();
        }
    }
}
</script>
