<template>
    <div class="cw-tools cw-tools-contents">
        <courseware-tree v-if="structuralElements.length" />
    </div>
</template>

<script>
import CoursewareTree from './CoursewareTree.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-tools-contents',
    components: {
        CoursewareTree,
    },

    computed: {
        ...mapGetters({
            structuralElements: 'courseware-structural-elements/all',
        }),
    },
};
</script>
