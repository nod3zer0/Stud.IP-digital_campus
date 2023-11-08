<template>
  <ul class="widget-list widget-links sidebar-views cw-view-widget">
        <li :class="{ active: templatesView }">
            <a href="#" @click.prevent="setTemplatesView">
                <translate>Vorlagen</translate>
            </a>
        </li>
  </ul>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-admin-view-widget',
    computed: {
        ...mapGetters({
            adminViewMode: 'adminViewMode'
        }),
        templatesView() {
            return this.adminViewMode === 'templates';
        },
    },
    methods: {
        ...mapActions({
            setAdminViewMode: 'adminViewMode'
        }),
        setTemplatesView() {
            this.setAdminViewMode('templates');
        },
    }
}
</script>
