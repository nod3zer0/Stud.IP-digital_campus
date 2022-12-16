<template>
    <div class="cw-tree" ref="tree">
        <template v-if="editMode">
                <span aria-live="assertive" class="assistive-text">{{ assistiveLive }}</span>
                <span id="operation" class="assistive-text">
                    {{$gettext('Drücken Sie die Leertaste, um neu anzuordnen')}}
                </span>
        </template>
        <ol v-if="!processing" class="cw-tree-root-list" role="listbox">
            <courseware-tree-item
                class="cw-tree-item"
                :element="rootElementWithNestedChildren"
                :currentElement="currentElement"
                @sort="sort"
                @moveItemUp="moveItemUp"
                @moveItemDown="moveItemDown"
                @moveItemPrevLevel="moveItemPrevLevel"
                @moveItemNextLevel="moveItemNextLevel"
                @childrenUpdated="updateNestedChildren"
            ></courseware-tree-item>
        </ol>
        <studip-progress-indicator
            v-else 
            :description="$gettext('Vorgang wird bearbeitet...')"
        />
    </div>
</template>

<script>
import CoursewareTreeItem from './CoursewareTreeItem.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    components: { 
        CoursewareTreeItem,
        StudipProgressIndicator
    },
    name: 'courseware-tree',
    data() {
        return {
            nestedChildren: [],
            processing: false,
            rootElementWithNestedChildren: {},
        }
    },
    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware',
            relatedStructuralElement: 'courseware-structural-elements/related',
            structuralElementById: 'courseware-structural-elements/byId',
            childrenById: 'courseware-structure/children',
            viewMode: 'viewMode',   
            structuralElements: 'courseware-structural-elements/all',
            assistiveLive: 'assistiveLiveContents'
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
        editMode() {
            return this.viewMode === 'edit';
        },
    },
    methods: {
         ...mapActions({
            updateStructuralElement: 'updateStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            sortChildrenInStructualElements: 'sortChildrenInStructualElements',
            loadStructuralElement: 'loadStructuralElement',
            setAssistiveLiveContents: 'setAssistiveLiveContents'
         }),
        updateNestedChildren() {
            this.nestedChildren = this.getNestedChildren(this.rootElement);
            this.setRootElementWithNestedChildren();
        },
        setRootElementWithNestedChildren() {
            let element = { id: this.rootElement.id, attributes: this.rootElement.attributes };
            element.nestedChildren = this.nestedChildren;

            this.rootElementWithNestedChildren = element;
        },
        getNestedChildren(structuralElement) {
            let children = _.cloneDeep(this.structuralElements
                .filter(
                    element => element.relationships.parent?.data?.id === structuralElement.id
                )).sort((a,b) => a.attributes.position - b.attributes.position);

            let nestedChildren = [];
            for (let child of children) {
                child.nestedChildren = this.getNestedChildren(child);
                nestedChildren.push(child);
            }

            return nestedChildren;
        },
        async sort(data) {
            const tree = this.$refs.tree;
            const currentScrollPosition = tree.offsetParent.scrollTop;
            this.processing = true;
            if (data.oldParent !== data.newParent) {
                await this.lockObject({ id: data.id, type: 'courseware-structural-elements' });
                let element = this.structuralElementById({ id: data.id });
                element.relationships.parent.data.id = data.newParent;
                await this.updateStructuralElement({
                    element: element,
                    id: element.id,
                });
                await this.unlockObject({ id: data.id, type: 'courseware-structural-elements' });
                await this.loadStructuralElement(data.id);
            }
            await this.loadStructuralElement(data.newParent);
            const parent = this.structuralElementById({ id: data.newParent });
            await this.sortChildrenInStructualElements({parent: parent, children: data.sortArray});
            this.updateNestedChildren();
            this.processing = false;
            this.$nextTick(() => {
                tree.offsetParent.scrollTop = currentScrollPosition;
            });
        },
        moveItemUp(data) {
            data.direction = 'up';
            this.reorderNestedChildren(data);
        },
        moveItemDown(data) {
            data.direction = 'down';
            this.reorderNestedChildren(data);
        },
        moveItemPrevLevel(data) {
            data.direction = 'prev';
            this.reorderNestedChildren(data);
        },
        moveItemNextLevel(data) {
            data.direction = 'next';
            this.reorderNestedChildren(data);
        },
        reorderNestedChildren(data) {
            this.rootElementWithNestedChildren = this.recursiveNestedChildrenUpdate(this.rootElementWithNestedChildren, data);
        },
        recursiveNestedChildrenUpdate(element, data) {
            if (data.direction === 'prev' && data.parents[1] && element.id === data.parents[1]) {
                //element is grandparent
                let parentIndex = element.nestedChildren.findIndex((e) => e.id === data.parents[0]);
                let movingElementIndex = element.nestedChildren[parentIndex].nestedChildren.findIndex((e) => e.id === data.element.id);
                const newPos = parentIndex + 1;
                element.nestedChildren.splice(newPos, 0, element.nestedChildren[parentIndex].nestedChildren[movingElementIndex]);
                element.nestedChildren[parentIndex].nestedChildren.splice(movingElementIndex, 1);

                element.nestedChildren[newPos].newPos = newPos;
                element.nestedChildren[newPos].newParentId =  parseInt(element.id);
                element.nestedChildren[newPos].sortArray = element.nestedChildren.map(c => {return {id: c.id, type: 'courseware-structural-elements'}});
                element.nestedChildren[newPos].moveDirection = data.direction;

                const assistiveLive = this.$gettextInterpolate(
                    this.$gettext('%{elementTitle} eine Ebene nach oben bewegt. Übergeordnete Seite: %{parentTitle}. Aktuelle Position in der Liste: %{pos} von %{listLength}'), 
                    { elementTitle: data.element.attributes.title, parentTitle: element.attributes.title, pos: newPos + 1, listLength: element.nestedChildren[newPos].sortArray.length }
                );
                this.setAssistiveLiveContents(assistiveLive);
            }
            if (element.id === data.parents[0]) {
                if (data.direction === 'up' || data.direction === 'down') {
                    const elementIndex = element.nestedChildren.findIndex((e) => e.id === data.element.id);
                    let vertical = data.direction === 'up' ? -1 : data.direction === 'down' ? 1 : 0;
                    const newPos = elementIndex + vertical;
                    if (newPos >= 0 && newPos < element.nestedChildren.length) {
                        element.nestedChildren.splice(newPos, 0, element.nestedChildren.splice(elementIndex, 1)[0]);
                        element.nestedChildren[newPos].newPos = newPos;
                        element.nestedChildren[newPos].newParentId = parseInt(element.id);
                        element.nestedChildren[newPos].sortArray = element.nestedChildren.map(c => {return {id: c.id, type: 'courseware-structural-elements'}});
                        element.nestedChildren[newPos].moveDirection = data.direction;

                        const assistiveLive = this.$gettextInterpolate(
                            this.$gettext('%{elementTitle} bewegt. Aktuelle Position in der Liste: %{pos} von %{listLength}'), 
                            { elementTitle: data.element.attributes.title, pos: newPos + 1, listLength: element.nestedChildren[newPos].sortArray.length }
                        );
                        this.setAssistiveLiveContents(assistiveLive);
                    }
                }
                if (data.direction === 'next') {
                    const elementIndex = element.nestedChildren.findIndex((e) => e.id === data.element.id);
                    if (elementIndex !== 0) {
                        const newParentIndex = elementIndex - 1;
                        element.nestedChildren[newParentIndex].nestedChildren.push(element.nestedChildren[elementIndex]);
                        element.nestedChildren.splice(elementIndex, 1);
                        const newPos = element.nestedChildren[newParentIndex].nestedChildren.length - 1;
                        element.nestedChildren[newParentIndex].nestedChildren[newPos].newPos = newPos;
                        const newParentId = element.nestedChildren[newParentIndex].id;
                        element.nestedChildren[newParentIndex].nestedChildren[newPos].newParentId = parseInt(newParentId);
                        element.nestedChildren[newParentIndex].nestedChildren[newPos].sortArray = element.nestedChildren[newParentIndex].nestedChildren.map(c => {return {id: c.id, type: 'courseware-structural-elements'}});

                        const assistiveLive = this.$gettextInterpolate(
                            this.$gettext('%{elementTitle} eine Ebene nach unten bewegt. Übergeordnete Seite: %{parentTitle}. Aktuelle Position in der Liste: %{pos} von %{listLength}'), 
                            { elementTitle: data.element.attributes.title, parentTitle: element.nestedChildren[newParentIndex].attributes.title, pos: newPos + 1, listLength: element.nestedChildren[newParentIndex].nestedChildren[newPos].sortArray.length }
                        );
                        this.setAssistiveLiveContents(assistiveLive);
                    }
                }
            } else {
                element.nestedChildren.forEach((child,index) => {
                    element.nestedChildren[index] = this.recursiveNestedChildrenUpdate(child, data);
                });
            }

            return element;
        },
    },
    mounted() {
        this.updateNestedChildren();
    }
};
</script>
