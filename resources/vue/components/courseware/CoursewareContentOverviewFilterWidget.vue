<template>
    <div class="cw-filter-widget">
        <form class="default" @submit.prevent="">
            <label>
                <translate>Lernmaterialien</translate>
                <select v-model="sourceFilter">
                    <option value="all">
                        <translate>Alle</translate>
                    </option>
                    <option value="personal">
                        <translate>Persönliche</translate>
                    </option>
                    <option value="shared">
                        <translate>Geteilte</translate>
                    </option>
                </select>
            </label>
            <label>
                <translate>Zweck</translate>
                <select v-model="purposeFilter">
                    <option value="all">
                        <translate>Alle</translate>
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
            </label>
            <label>
                <translate>Rechte</translate>
                <select v-model="permissionFilter">
                    <option value="read">
                        <translate>Lesen</translate>
                    </option>
                    <option value="write">
                        <translate>Lesen und schreiben</translate>
                    </option>
                </select>
            </label>
        </form>
    </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'courseware-content-overview-filter-widget',
    data() {
        return {
            permissionFilter: 'read',
            purposeFilter: 'all',
            sourceFilter: 'all',
        };
    },
    methods: {
        ...mapActions({
            setPermissionFilter: 'setPermissionFilter',
            setPurposeFilter: 'setPurposeFilter',
            setSourceFilter: 'setSourceFilter',
        }),
        filterPermission() {
            this.setPermissionFilter(this.permissionFilter);
        },
        filterPurpose() {
            this.setPurposeFilter(this.purposeFilter);
        },
        filterSource() {
            this.setSourceFilter(this.sourceFilter);
        },
    },
    watch: {
        permissionFilter() {
            this.filterPermission();
        },
        purposeFilter() {
            this.filterPurpose();
        },
        sourceFilter() {
            this.filterSource();
        },
    }
}
</script>
