<template>
  <div class="cw-content-overview">
        <courseware-content-overview-elements />
        <MountingPortal mountTo="#courseware-content-overview-action-widget" name="sidebar-views">
            <courseware-content-overview-action-widget />
        </MountingPortal>
        <MountingPortal mountTo="#courseware-content-overview-filter-widget" name="sidebar-views">
            <courseware-content-overview-filter-widget />
        </MountingPortal>
  </div>
</template>

<script>
import CoursewareContentOverviewElements from './CoursewareContentOverviewElements.vue';
import CoursewareContentOverviewActionWidget from './CoursewareContentOverviewActionWidget.vue';
import CoursewareContentOverviewFilterWidget from './CoursewareContentOverviewFilterWidget.vue';

export default {
    components: {
        CoursewareContentOverviewElements,
        CoursewareContentOverviewActionWidget,
        CoursewareContentOverviewFilterWidget
    }
}
</script>
