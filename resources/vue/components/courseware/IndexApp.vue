<template>
    <div v-if="courseware">
        <courseware-structural-element
            :structural-element="selected"
            :ordered-structural-elements="orderedStructuralElements"
            @select="selectStructuralElement"
        ></courseware-structural-element>
        <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions">
            <courseware-action-widget :structural-element="selected"></courseware-action-widget>
        </MountingPortal>
        <MountingPortal mountTo="#courseware-view-widget" name="sidebar-views">
            <courseware-view-widget></courseware-view-widget>
        </MountingPortal>
    </div>
    <div v-else><translate>Inhalte werden geladen</translate>...</div>
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
    data: () => ({
        selected: null,
        orderedStructuralElements: [],
    }),
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            relatedStructuralElement: 'courseware-structural-elements/related',
            structuralElements: 'courseware-structural-elements/all',
            structuralElementById: 'courseware-structural-elements/byId',
            userId: 'userId',
        }),
    },
    methods: {
        ...mapActions([
            'coursewareBlockAdder',
            'loadCoursewareStructure',
            'loadStructuralElement',
            'loadTeacherStatus',
        ]),
        async selectStructuralElement(id) {
            if (!id) {
                return;
            }

            await this.loadStructuralElement(id);
            this.selected = this.structuralElementById({ id });
        },
    },
    async mounted() {
        await this.loadCoursewareStructure();
        await this.loadTeacherStatus(this.userId);
        const selectedId = this.$route.params?.id;
        await this.selectStructuralElement(selectedId);
    },
    watch: {
        $route(to) {
            // reset block adder on navigate
            this.coursewareBlockAdder({});

            const selectedId = to.params?.id;
            this.selectStructuralElement(selectedId);
        },
        structuralElements(newElements, oldElements) {
            const nodes = buildNodes(this.structuralElements, this.relatedStructuralElement.bind(this));
            this.orderedStructuralElements = [...visitTree(nodes, findRoot(nodes))];
        },
    },
};

function buildNodes(structuralElements, relatedStructuralElement) {
    return structuralElements.reduce((memo, element) => {
        if (element.attributes['can-read']) {
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
        }

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
