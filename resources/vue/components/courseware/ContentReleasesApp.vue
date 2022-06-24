<template>
  <div class="cw-content-releases">
        <courseware-content-links />
        <courseware-companion-overlay />
  </div>
</template>

<script>
import CoursewareContentLinks from './CoursewareContentLinks.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';

export default {
    components: {
        CoursewareContentLinks,
        CoursewareCompanionOverlay
    }
}
</script>
