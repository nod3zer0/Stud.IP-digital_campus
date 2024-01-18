<template>
    <div class="studip-tree-child-description">
        <studip-loading-skeleton v-if="isLoading" />
        <div v-else v-translate="{ count: courseCount }" :translate-n="courseCount"
             translate-plural="<strong>%{count}</strong> Veranstaltungen">
            <strong>Eine</strong> Veranstaltung
        </div>
    </div>
</template>

<script>
import { TreeMixin } from '../../mixins/TreeMixin';
import StudipLoadingSkeleton from '../StudipLoadingSkeleton.vue';

export default {
    name: 'TreeNodeCourseInfo',
    components: { StudipLoadingSkeleton },
    mixins: [ TreeMixin ],
    props: {
        node: {
            type: Object,
            required: true
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
    data() {
        return {
            isLoading: false,
            courseCount: 0,
            showingAllCourses: false
        }
    },
    methods: {
        showAllCourses(state) {
            this.showingAllCourses = state;
            this.$emit('showAllCourses', state);
        },
        loadNodeInfo(node) {
            this.isLoading = true;
            this.getNodeCourseInfo(node, this.semester, this.semClass)
                .then(info => {
                    this.courseCount = info?.data.courses;
                    this.isLoading = false;
                });
        }
    },
    mounted() {
        this.loadNodeInfo(this.node);
    },
    watch: {
        node(newNode) {
            this.loadNodeInfo(newNode);
        }
    }
}
</script>
