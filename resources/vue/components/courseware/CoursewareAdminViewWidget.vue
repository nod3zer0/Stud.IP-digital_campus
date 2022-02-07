<template>
  <ul class="widget-list widget-links sidebar-views cw-view-widget">
        <li
            :class="{ active: templatesView }"
            @click="setTemplatesView"
        >
            <translate>Vorlagen</translate>
        </li>
  </ul>
</template>

<script>

export default {
    name: 'courseware-admin-view-widget',
    computed: {
        adminViewMode() {
            return this.$store.getters.adminViewMode;
        },
        templatesView() {
            return this.adminViewMode === 'templates';
        },
    },
    methods: {
        setTemplatesView() {
            this.$store.dispatch('adminViewMode', 'templates');
        },
    }

}
</script>