<template>
    <div class="cw-tree">
        <ul class="cw-tree-root-list">
            <courseware-tree-item
                class="cw-tree-item"
                :element="rootElement"
                :currentElement="currentElement"
            ></courseware-tree-item>
        </ul>
    </div>
</template>

<script>
import CoursewareTreeItem from './CoursewareTreeItem.vue';
import { mapGetters } from 'vuex';

export default {
    components: { CoursewareTreeItem },
    name: 'courseware-tree',
    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware',
            relatedStructuralElement: 'courseware-structural-elements/related',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        currentElement() {
            const id = this.$route?.params?.id;
            if (!id) {
                return null;
            }

            return this.structuralElementById({ id }) ?? null;
        },

        rootElement() {
            if (this.context.type !== 'public') {
                    const root = this.relatedStructuralElement({
                    parent: { id: this.courseware.id, type: this.courseware.type },
                    relationship: 'root',
                });

                return root;
            } else {
                return this.structuralElementById({ id: this.context.rootId });
            }

        },
    },
};
</script>
