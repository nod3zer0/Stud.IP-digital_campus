<template>
    <select v-model="purposeFilter" class="sidebar-selectlist">
        <option value="all">
            <translate>alle</translate>
        </option>
        <option value="content">
            <translate>Inhalt</translate>
        </option>
        <option value="template">
            <translate>Aufgabenvorlage</translate>
        </option>
        <option value="oer">
            <translate>OER-Material</translate>
        </option>
        <option value="portfolio">
            <translate>ePortfolio</translate>
        </option>
        <option value="draft">
            <translate>Entwurf</translate>
        </option>
        <option value="other">
            <translate>Sonstiges</translate>
        </option>
    </select>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-content-overview-filter-widget',
    data() {
        return {
            purposeFilter: 'all'
        };
    },
    methods: {
        ...mapActions({
            setPurposeFilter: 'setPurposeFilter'
        }),
        filterPurpose() {
            this.setPurposeFilter(this.purposeFilter);
        }
    },
    watch: {
        purposeFilter() {
            this.filterPurpose();
        }
    }
}
</script>