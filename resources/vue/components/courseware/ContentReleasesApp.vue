<template>
  <div class="cw-content-releases">
        <courseware-content-links />
        <courseware-content-shared />
        <courseware-companion-overlay />
  </div>
</template>

<script>
import CoursewareContentLinks from './CoursewareContentLinks.vue';
import CoursewareContentShared from './CoursewareContentShared.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';

export default {
    components: {
        CoursewareContentLinks,
        CoursewareContentShared,
        CoursewareCompanionOverlay
    },

}
</script>
