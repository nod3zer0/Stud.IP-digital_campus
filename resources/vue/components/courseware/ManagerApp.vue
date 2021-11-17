<template>
    <courseware-course-manager></courseware-course-manager>
</template>

<script>
import CoursewareCourseManager from './CoursewareCourseManager.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    components: { CoursewareCourseManager },
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            structuralElements: 'courseware-structural-elements/all',
        }),
    },
    methods: {
        ...mapActions({
            buildStructure: 'courseware-structure/build',
            invalidateStructureCache: 'courseware-structure/invalidateCache',
            loadCoursewareStructure: 'courseware-structure/load',
        }),
    },
    async mounted() {
        await this.loadCoursewareStructure();
    },
    watch: {
        async structuralElements(newElements, oldElements) {
            // compute order of structural elements once more
            await this.buildStructure();

            // throw away stale cache
            this.invalidateStructureCache();
        },
    },
};
</script>
