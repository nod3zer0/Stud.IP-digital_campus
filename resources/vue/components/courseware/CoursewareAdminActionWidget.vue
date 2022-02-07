<template>
    <ul class="widget-list widget-links cw-action-widget">
        <li v-if="templatesView" class="cw-action-widget-add" @click="addTemplate">
            <translate>Vorlage hinzufügen</translate>
        </li>
    </ul>
</template>

<script>

export default {
    name: 'courseware-admin-action-widget',
    computed: {
        adminViewMode() {
            return this.$store.getters.adminViewMode;
        },
        templatesView() {
            return this.adminViewMode === 'templates';
        },
        showAddTemplateDialog() {
            return this.$store.getters.showAddTemplateDialog;
        },
    },
    methods: {
        addTemplate() {
            this.$store.dispatch('showAddTemplateDialog', true);
        }
    }
}
</script>