<template>
    <div class="cw-admin">
        <courseware-admin-templates v-if="templatesView" />
        <MountingPortal mountTo="#courseware-admin-view-widget" name="sidebar-views">
            <courseware-admin-view-widget />
        </MountingPortal>
        <MountingPortal mountTo="#courseware-admin-action-widget" name="sidebar-views">
            <courseware-admin-action-widget />
        </MountingPortal>

    </div>
</template>

<script>
import CoursewareAdminActionWidget from './CoursewareAdminActionWidget.vue';
import CoursewareAdminTemplates from './CoursewareAdminTemplates.vue';
import CoursewareAdminViewWidget from './CoursewareAdminViewWidget.vue';
export default {
    components: {
        CoursewareAdminActionWidget,
        CoursewareAdminTemplates,
        CoursewareAdminViewWidget
    },
    computed: {
        adminViewMode() {
            return this.$store.getters.adminViewMode;
        },
        templatesView() {
            return this.adminViewMode === 'templates';
        },
    },
}
</script>