<template>
    <li>
        <div :class="{ 'cw-tree-item-is-root': isRoot, 'cw-tree-item-first-level': isFirstLevel }">
            <router-link
                :to="'/structural_element/' + element.id"
                class="cw-tree-item-link"
                :class="{ 'cw-tree-item-link-current': isCurrent }"
            >
                {{ element.attributes.title }}
            </router-link>
        </div>
        <ul v-if="hasChildren" :class="{ 'cw-tree-chapter-list': isRoot }">
            <courseware-tree-item
                v-for="child in children"
                :key="child.id"
                :element="child"
                :currentElement="currentElement"
                :depth="depth + 1"
                class="cw-tree-item"
            />
        </ul>
    </li>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-tree-item',
    props: {
        element: {
            type: Object,
            required: true,
        },
        currentElement: {
            type: Object,
        },
        depth: {
            type: Number,
            default: 0,
        },
    },
    computed: {
        ...mapGetters({
            childrenById: 'courseware-structure/children',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        children() {
            if (!this.element) {
                return [];
            }

            return this.childrenById(this.element.id)
                .map((id) => this.structuralElementById({ id }))
                .filter(Boolean);
        },
        hasChildren() {
            return this.childrenById(this.element.id).length;
        },
        isRoot() {
            return this.depth === 0;
        },
        isFirstLevel() {
            return this.depth === 1;
        },
        isCurrent() {
            return this.element.id === this.currentElement?.id;
        },
    },
};
</script>
