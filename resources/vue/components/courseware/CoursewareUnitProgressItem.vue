<template>
    <a 
        href="#"
        class="cw-unit-progress-item"
        :title="name"
        @click="$emit('selectChapter', chapterId)"
    >
        <div class="cw-unit-progress-item-value">
            <courseware-progress-circle :value="parseInt(value)" />
        </div>
        <div class="cw-unit-progress-item-description">
            {{ name }}
        </div>
    </a>
</template>

<script>
import CoursewareProgressCircle from './CoursewareProgressCircle.vue';

export default {
    name: 'courseware-unit-progress-item',
    components: {
        CoursewareProgressCircle,
    },
    props: {
        name: String,
        value: Number,
        chapterId: Number,
    },
};
</script>
