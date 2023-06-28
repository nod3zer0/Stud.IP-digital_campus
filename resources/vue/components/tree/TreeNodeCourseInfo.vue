<template>
    <div class="studip-tree-child-description">
        <template v-if="showingAllCourses">
            <div v-translate="{ count: courseCount }" :translate-n="courseCount"
                  translate-plural="<strong>%{count}</strong> Veranstaltungen auf dieser Ebene.">
                <strong>Eine</strong> Veranstaltung auf dieser Ebene.
            </div>
        </template>
        <div v-else v-translate="{ count: courseCount }" :translate-n="courseCount"
              translate-plural="<strong>%{count}</strong> Veranstaltungen auf dieser Ebene.">
            <strong>Eine</strong> Veranstaltung auf dieser Ebene.
        </div>
        <template v-if="!showingAllCourses">
            <div v-translate="{ count: allCourseCount }" :translate-n="allCourseCount"
                  translate-plural="<strong>%{count}</strong> Veranstaltungen auf allen Unterebenen.">
                <strong>Eine</strong> Veranstaltung auf allen Unterebenen.
            </div>
        </template>
        <div v-else v-translate="{ count: allCourseCount }" :translate-n="allCourseCount"
              translate-plural="<strong>%{count}</strong> Veranstaltungen auf allen Unterebenen.">
            <strong>Eine</strong> Veranstaltung auf allen Unterebenen.
        </div>
    </div>
</template>

<script>
import { TreeMixin } from '../../mixins/TreeMixin';

export default {
    name: 'TreeNodeCourseInfo',
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
            courseCount: 0,
            allCourseCount: 0,
            showingAllCourses: false
        }
    },
    methods: {
        showAllCourses(state) {
            this.showingAllCourses = state;
            this.$emit('showAllCourses', state);
        }
    },
    mounted() {
        this.getNodeCourseInfo(this.node, this.semester, this.semClass)
            .then(info => {
                this.courseCount = info?.data.courses;
                this.allCourseCount = info?.data.allCourses;
            });
    },
    watch: {
        node(newNode) {
            this.getNodeCourseInfo(newNode, this.semester, this.semClass)
                .then(info => {
                    this.courseCount = info?.data.courses;
                    this.allCourseCount = info?.data.allCourses;
                });
        }
    }
}
</script>
