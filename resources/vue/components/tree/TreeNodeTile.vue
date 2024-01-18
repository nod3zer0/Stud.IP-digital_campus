<template>
    <a :href="url" @click.prevent="openNode" :title="$gettextInterpolate($gettext('Unterebene %{ node } öffnen'),
                                { node: node.attributes.name })">
        <p class="studip-tree-child-title">
            {{ node.attributes.name }}
        </p>

        <tree-node-course-info v-if="node.attributes.ancestors.length > 2" 
                               :node="node"
                               :semester="semester"
                               :sem-class="semClass"
        ></tree-node-course-info>
    </a>
</template>

<script>
import TreeNodeCourseInfo from './TreeNodeCourseInfo.vue';
export default {
    name: 'TreeNodeTile',
    components: { TreeNodeCourseInfo },
    props: {
        node: {
            type: Object,
            required: true
        },
        url: {
            type: String,
            required: true
        },
        withChildren: {
            type: Boolean,
            default: true
        },
        semester: {
            type: String,
            default: 'all'
        },
        semClass: {
            type: Number,
            default: 0
        }
    },
    methods: {
        openNode() {
            STUDIP.eventBus.emit('open-tree-node', this.node);
        }
    }
}
</script>
