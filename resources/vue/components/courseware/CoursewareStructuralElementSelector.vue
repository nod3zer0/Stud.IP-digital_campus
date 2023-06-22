<template>
    <ul class="courseware-structural-element-selector" role="radiogroup">
        <courseware-structural-element-selector-item 
            v-if="rootElement !== null"
            :element="rootElement"
            :siblings="[]"
            :selectedId="selectedId"
            :focusedElementId="focusedElementId"
            :rootId="rootId"
            :validateAncestors="validateAncestors"
            :targetId="targetId"
            :targetAncestors="targetAncestors"
            :selectablePurposes="selectablePurposes"
            @input="handleInput"
            @focus="handleFocus"
        />
    </ul>
</template>

<script>
import CoursewareStructuralElementSelectorItem from './CoursewareStructuralElementSelectorItem.vue';
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'courseware-structural-element-selector',
    components: {
        CoursewareStructuralElementSelectorItem
    },
    model: {
        prop: 'element'
    },
    props: {
        element: {
            type: Object
        },
        rootId: {
            type: String,
            required: true
        },  
        validateAncestors: {
            type: Boolean,
            default: false
        },
        targetId: {
            type: String,
            default: null
        },
        selectablePurposes: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            rootElement: null,
            focusedElementId: '' 
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            coursewareUnits: 'courseware-units/all',
            structuralElementById: 'courseware-structural-elements/byId',
            context: 'context',
            childrenById: 'courseware-structure/children',
            currentElement: 'currentElement'
        }),
        children() {
            if (!this.rootElement) {
                return [];
            }

            return this.childrenById(this.rootElement.id)
                .map((id) => this.structuralElementById({ id }))
                .filter(Boolean);
        },
        selectedId() {
            return this.element?.id ?? '';
        },
        targetElement() {
            return this.structuralElementById({ id: this.targetId });
        },
        targetAncestors() {
            if (!this.targetElement || !this.validateAncestors) {
                return [];
            }

            const finder = (parent) => {
                const parentId = parent.relationships?.parent?.data?.id;
                if (!parentId) {
                    return null;
                }
                const element = this.structuralElementById({ id: parentId });
                if (!element) {
                    console.error(`CoursewareStructuralElement#ancestors: Could not find parent by ID: "${parentId}".`);
                }

                return element;
            };

            const visitAncestors = function* (node) {
                const parent = finder(node);
                if (parent) {
                    yield parent;
                    yield* visitAncestors(parent);
                }
            };

            return [...visitAncestors(this.targetElement)].reverse();
        },
    },
    methods: {
        ...mapActions({
            loadStructuralElement: 'courseware-structural-elements/loadById',
            companionError: 'companionError',
            companionSuccess: 'companionSuccess',
        }),
        handleInput(id) {
            this.$emit('input', this.structuralElementById({ id }));
            this.focusedElementId = id;
        },
        handleFocus(id) {
            this.focusedElementId = id;
        },
        async loadRootElement(rootId) {
            this.rootElement = null;
            await this.loadStructuralElement({id: rootId, options: {include: 'children'}});
            this.rootElement = this.structuralElementById({ id: rootId});
        }
    },
    async mounted() {
        await this.loadRootElement(this.rootId);
    },
    watch: {
        async rootId(newRootId) {
            await this.loadRootElement(newRootId);
            this.focusedElementId = '';
        }
    }
};
</script>
