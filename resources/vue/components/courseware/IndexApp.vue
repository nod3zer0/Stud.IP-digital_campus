<template>
    <div v-if="courseware">
        <courseware-structural-element
            :ordered-structural-elements="orderedStructuralElements"
        ></courseware-structural-element>
        <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions">
            <courseware-action-widget></courseware-action-widget>
        </MountingPortal>
        <MountingPortal mountTo="#courseware-view-widget" name="sidebar-views">
            <courseware-view-widget></courseware-view-widget>
        </MountingPortal>
    </div>
    <div v-else>
        <translate>Inhalte werden geladen</translate>...
    </div>
</template>

<script>
import CoursewareStructuralElement from './CoursewareStructuralElement.vue';
import CoursewareViewWidget from './CoursewareViewWidget.vue';
import CoursewareActionWidget from './CoursewareActionWidget.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareStructuralElement,
        CoursewareViewWidget,
        CoursewareActionWidget,
    },
    data: () => ({ orderedStructuralElements: [] }),
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            relatedStructuralElement: 'courseware-structural-elements/related',
            structuralElements: 'courseware-structural-elements/all',
            userId: 'userId',
        }),
    },
    methods: {
        ...mapActions(['loadCoursewareStructure', 'loadTeacherStatus', 'coursewareBlockAdder']),
    },
    async mounted() {
        await this.loadCoursewareStructure();
        await this.loadTeacherStatus(this.userId);
    },
    watch: {
        $route() {
            this.coursewareBlockAdder({}); //reset block adder on navigate
        },
        structuralElements(newElements, oldElements) {
            const nodes = buildNodes(this.structuralElements, this.relatedStructuralElement.bind(this));
            this.orderedStructuralElements = [...visitTree(nodes, findRoot(nodes))];
        },
    },
};

function buildNodes(structuralElements, relatedStructuralElement) {
    return structuralElements.reduce((memo, element) => {
        memo.push({
            id: element.id,
            parent:
                relatedStructuralElement({
                    parent: element,
                    relationship: 'parent',
                })?.id ?? null,

            children:
                relatedStructuralElement({
                    parent: element,
                    relationship: 'children',
                })?.map((child) => child.id) ?? [],
        });

        return memo;
    }, []);
}

function findRoot(nodes) {
    return nodes.find((node) => node.parent === null);
}

function findNode(nodes, id) {
    return nodes.find((node) => node.id === id);
}

function* visitTree(nodes, current) {
    if (current) {
        yield current.id;

        for (let index = 0; index < current.children.length; index++) {
            yield* visitTree(nodes, findNode(nodes, current.children[index]));
        }
    }
}
</script>
