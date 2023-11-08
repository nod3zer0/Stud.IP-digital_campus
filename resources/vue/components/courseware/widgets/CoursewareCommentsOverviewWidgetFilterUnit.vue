<template>
        <sidebar-widget :title="$gettext('Lernmaterial')">
        <template #content>
            <div class="cw-filter-widget">
                <form class="default" @submit.prevent="">
                    <select v-model="unitFilter" :aria-label="$gettext('Filter: Lernmaterial')">
                        <option value="all">
                            {{ $gettext('Alle') }}
                        </option>
                        <option v-for="unit in sortedUnits" :key="unit.id" :value="unit.id">
                            {{ getUnitName(unit) }}
                        </option>
                    </select>
                </form>
            </div>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../../SidebarWidget.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-comments-overview-widget-filter-unit',
    data() {
        return {
            unitFilter: 'all',
        };
    },
    computed: {
        ...mapGetters({
            units: 'courseware-units/all',
            elementById: 'courseware-structural-elements/byId',
        }),
        sortedUnits() {
            let units = _.cloneDeep(this.units);
            units = units.sort((a, b) => this.getUnitName(a) < this.getUnitName(b) ? -1 : 1);

            return units;
        }
    },
    methods: {
        ...mapActions({
            setUnitFilter: 'setUnitFilter',
        }),
        filterUnit() {
            this.setUnitFilter(this.unitFilter);
        },
        getUnitName(unit) {
            return this.elementById({ id: unit.relationships['structural-element'].data.id}).attributes.title;
        }
    },
    watch: {
        unitFilter() {
            this.filterUnit();
        },
    }
};
</script>
