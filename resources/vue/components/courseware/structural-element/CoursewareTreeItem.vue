<template>
    <li v-if="showItem"
        :draggable="editMode ? true : null"
        :aria-selected="editMode ? keyboardSelected : null"
    >
        <div class="cw-tree-item-wrapper" v-if="showRootElement || depth > 0">
            <span
                v-if="editMode && depth > 0 && canEdit"
                class="cw-sortable-handle"
                :tabindex="0"
                aria-describedby="operation"
                ref="sortableHandle"
                role="button"
                @keydown="handleKeyEvent"
            >
            </span>
            <courseware-tree-item-updater
                v-if="editMode && editingItem"
                :structuralElement="element"
                @close="editingItem = false"
                @childrenUpdated="$emit('childrenUpdated')"
            />
            <router-link
                v-else
                :to="'/structural_element/' + element.id"
                class="cw-tree-item-link"
                :class="{
                    'cw-tree-item-link-current': isCurrent,
                    'cw-tree-item-link-edit': editMode,
                    'cw-tree-item-link-selected': keyboardSelected,
                }"
            >
                {{ element.attributes?.title || '–' }}
                <button v-if="editMode && canEdit" class="cw-tree-item-edit-button" @click.prevent="editingItem = true">
                    <studip-icon shape="edit" />
                </button>

                <span v-if="task">| {{ solverName }}</span>
                <span
                    v-if="hasReleaseOrWithdrawDate"
                    class="cw-tree-item-flag-date"
                    :title="$gettext('Diese Seite hat eine zeitlich beschränkte Sichtbarkeit')"
                ></span>
                <span
                    v-if="hasWriteApproval"
                    class="cw-tree-item-flag-write"
                    :title="$gettext('Diese Seite kann von Teilnehmenden bearbeitet werden')"
                ></span>
                <span
                    v-if="hasNoReadApproval"
                    class="cw-tree-item-flag-cant-read"
                    :title="$gettext('Diese Seite kann von Teilnehmenden nicht gesehen werden')"
                ></span>
                <template v-if="!userIsTeacher && inCourse">
                    <span
                        v-if="complete"
                        class="cw-tree-item-sequential cw-tree-item-sequential-complete"
                        :title="$gettext('Diese Seite wurde von Ihnen vollständig bearbeitet')"
                    >
                    </span>
                    <span
                        v-else
                        class="cw-tree-item-sequential cw-tree-item-sequential-percentage"
                        :title="$gettextInterpolate($gettext('Fortschritt: %{progress}%'), { progress: itemProgress })"
                    >
                        {{ itemProgress }} %
                    </span>
                </template>
            </router-link>
        </div>
        <ol
            v-if="hasChildren && !editMode"
            :class="{
                'cw-tree-chapter-list': isRoot,
                'cw-tree-subchapter-list': isFirstLevel,
            }"
        >
            <courseware-tree-item
                v-for="child in children"
                :key="child.id"
                :element="child"
                :currentElement="currentElement"
                :depth="depth + 1"
                class="cw-tree-item"
            />
        </ol>
        <draggable
            v-if="editMode"
            :class="{ 'cw-tree-chapter-list-empty': nestedChildren.length === 0 }"
            tag="ol"
            :component-data="draggableData"
            class="cw-tree-draggable-list"
            handle=".cw-sortable-handle"
            v-bind="dragOptions"
            :elementId="element.id"
            :list="nestedChildren"
            :group="{ name: 'g1' }"
            @end="endDrag"
        >
            <courseware-tree-item
                v-for="el in nestedChildren"
                :key="el.id"
                :element="el"
                :currentElement="currentElement"
                :depth="depth + 1"
                :newPos="el.newPos"
                :newParentId="el.newParentId"
                :siblingCount="nestedChildren.length"
                class="cw-tree-item"
                :elementid="el.id"
                @sort="sort"
                @moveItemUp="moveItemUp"
                @moveItemDown="moveItemDown"
                @moveItemPrevLevel="moveItemPrevLevel"
                @moveItemNextLevel="moveItemNextLevel"
                @childrenUpdated="$emit('childrenUpdated')"
            />
        </draggable>
        <ol
            v-if="editMode && canEdit && isFirstLevel"
            class="cw-tree-adder-list"
        >
            <courseware-tree-item-adder :parentId="element.id" />
        </ol>
    </li>
</template>

<script>
import CoursewareTreeItemAdder from './CoursewareTreeItemAdder.vue';
import CoursewareTreeItemUpdater from './CoursewareTreeItemUpdater.vue';
import draggable from 'vuedraggable';

import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-tree-item',
    components: {
        CoursewareTreeItemAdder,
        CoursewareTreeItemUpdater,
        draggable,
    },
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
        keyboardSelectedProp: {
            type: Boolean,
            default: false,
        },
        newPos: {
            type: Number,
        },
        newParentId: {
            type: Number,
        },
        siblingCount: {
            type: Number,
        },
    },
    data() {
        return {
            keyboardSelected: false,
            editingItem: false,
        };
    },
    computed: {
        ...mapGetters({
            childrenById: 'courseware-structure/children',
            structuralElementById: 'courseware-structural-elements/byId',
            context: 'context',
            taskById: 'courseware-tasks/byId',
            userById: 'users/byId',
            groupById: 'status-groups/byId',
            viewMode: 'viewMode',
            courseware: 'courseware',
            progressData: 'progresses',
            userIsTeacher: 'userIsTeacher',
            showRootElement: 'showRootElement'
        }),
        draggableData() {
            return {
                attrs: {
                    role: 'listbox',
                    ['aria-label']: this.$gettextInterpolate(this.$gettext('Unterseiten von %{elementName}'), {
                        elementName: this.element.attributes?.title,
                    }),
                },
            };
        },
        children() {
            if (!this.element) {
                return [];
            }

            return this.childrenById(this.element.id)
                .map((id) => this.structuralElementById({ id }))
                .filter(Boolean)
                .sort((a, b) => a.attributes.position - b.attributes.position);
        },
        nestedChildren() {
            return this.element.nestedChildren ?? [];
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
        hasReleaseOrWithdrawDate() {
            return (
                this.element.attributes?.['release-date'] !== null ||
                this.element.attributes?.['withdraw-date'] !== null
            );
        },
        hasWriteApproval() {
            const writeApproval = this.element.attributes?.['write-approval'];

            if (!writeApproval || Object.keys(writeApproval).length === 0) {
                return false;
            }
            return (
                (writeApproval.all || writeApproval.groups.length > 0 || writeApproval.users.length > 0) &&
                this.element.attributes?.['can-edit']
            );
        },
        hasNoReadApproval() {
            if (this.context.type === 'users') {
                return false;
            }
            const readApproval = this.element.attributes?.['read-approval'];

            if (!readApproval || Object.keys(readApproval).length === 0 || this.hasWriteApproval) {
                return false;
            }
            return !readApproval.all && readApproval.groups.length === 0 && readApproval.users.length === 0;
        },
        hasPurposeClass() {
            return this.purposeClass !== '';
        },
        purposeClass() {
            if (
                (this.isFirstLevel && this.context.type === 'users') ||
                (this.context.type === 'courses' && this.element.attributes?.purpose === 'task')
            ) {
                return this.element.attributes?.purpose;
            }
            return '';
        },
        task() {
            if (this.element.relationships?.task?.data) {
                return this.taskById({
                    id: this.element.relationships?.task?.data?.id,
                });
            }

            return null;
        },
        taskProgress() {
            return this.task ? this.task.attributes.progress + '%' : '';
        },
        solver() {
            if (this.task) {
                const solver = this.task.relationships.solver.data;
                if (solver.type === 'users') {
                    return this.userById({ id: solver.id });
                }
                if (solver.type === 'status-groups') {
                    return this.groupById({ id: solver.id });
                }
            }

            return null;
        },
        solverName() {
            if (this.solver) {
                if (this.solver.type === 'users') {
                    return this.solver.attributes['formatted-name'];
                }
                if (this.solver.type === 'status-groups') {
                    return this.solver.attributes.name;
                }
            }

            return '';
        },
        isTask() {
            return this.element.attributes?.purpose === 'task';
        },
        showItem() {
            if (this.isTask) {
                return this.task !== undefined;
            }

            return true;
        },
        editMode() {
            return this.viewMode === 'edit';
        },
        dragOptions() {
            return {
                animation: 0,
                disabled: false,
                ghostClass: 'cw-tree-item-ghost',
            };
        },
        canEdit() {
            return this.element.attributes?.['can-edit'] ?? false;
        },
        inCourse() {
            return this.context.type === 'courses';
        },
        progress() {
            return this.progressData?.[this.element.id];
        },
        itemProgress() {
            return this.progress?.progress?.self ?? 0;
        },
        complete() {
            return this.itemProgress === 100;
        },
    },
    methods: {
        ...mapActions({
            loadTask: 'loadTask',
            setAssistiveLiveContents: 'setAssistiveLiveContents',
        }),
        endDrag(e) {
            let sortArray = [];
            for (let child of e.to.childNodes) {
                sortArray.push({ id: child.attributes.elementid.nodeValue, type: 'courseware-structural-elements' });
            }

            let data = {
                id: e.item._underlying_vm_.id,
                newPos: e.newIndex,
                oldPos: e.oldIndex,
                oldParent: e.item._underlying_vm_.relationships.parent.data.id,
                newParent: e.to.__vue__.$attrs.elementId,
                sortArray: sortArray,
            };

            if (data.oldParent === data.newParent && data.oldPos === data.newPos) {
                return;
            }
            if (data.oldParent !== data.newParent) {
                sortArray.splice(data.newPos, 0, { id: data.id, type: 'courseware-structural-elements' });
            }

            data.sortArray = sortArray;
            this.$emit('sort', data);
        },
        sort(data) {
            this.$emit('sort', data);
        },
        handleKeyEvent(e) {
            switch (e.keyCode) {
                case 13: // enter
                    e.preventDefault();
                    if (this.keyboardSelected) {
                        this.storeKeyboardSorting();
                    } else {
                        this.keyboardSelected = true;
                        const assistiveLive = this.$gettextInterpolate(
                            this.$gettext(
                                '%{elementTitle} ausgewählt. Aktuelle Position in der Liste: %{pos} von %{listLength}. Drücken Sie die Aufwärts- und Abwärtspfeiltasten, um die Position zu ändern, die Leertaste zum Ablegen, die Escape-Taste zum Abbrechen. Mit Pfeiltasten links und rechts kann die Position in der Hierarchie verändert werden.'
                            ),
                            {
                                elementTitle: this.element.attributes.title,
                                pos: this.element.attributes.position + 1,
                                listLength: this.siblingCount,
                            }
                        );

                        this.setAssistiveLiveContents(assistiveLive);
                    }
                    break;
            }
            if (this.keyboardSelected) {
                const data = {
                    element: this.element,
                    parents: [],
                };
                switch (e.keyCode) {
                    case 27: // esc
                    case 9: //tab
                        this.abortKeyboardSorting();
                        break;
                    case 37: // left
                        e.preventDefault();
                        this.$emit('moveItemPrevLevel', data);
                        break;
                    case 38: // up
                        e.preventDefault();
                        this.$emit('moveItemUp', data);
                        break;
                    case 39: // right
                        e.preventDefault();
                        this.$emit('moveItemNextLevel', data);
                        break;
                    case 40: // down
                        e.preventDefault();
                        this.$emit('moveItemDown', data);
                        break;
                }
            }
        },
        moveItemPrevLevel(data) {
            data.parents.push(this.element.id);
            this.$emit('moveItemPrevLevel', data);
        },
        moveItemUp(data) {
            data.parents.push(this.element.id);
            this.$emit('moveItemUp', data);
        },
        moveItemNextLevel(data) {
            data.parents.push(this.element.id);
            this.$emit('moveItemNextLevel', data);
        },
        moveItemDown(data) {
            data.parents.push(this.element.id);
            this.$emit('moveItemDown', data);
        },
        abortKeyboardSorting() {
            this.$emit('childrenUpdated');
            const assistiveLive = this.$gettextInterpolate(this.$gettext('%{elementTitle}. Neuordnung abgebrochen.'), {
                elementTitle: this.element.attributes.title,
            });
            this.setAssistiveLiveContents(assistiveLive);
            this.$nextTick(() => {
                this.keyboardSelected = false;
            });
        },
        storeKeyboardSorting() {
            const data = {
                id: this.element.id,
                newPos: this.element.newPos,
                oldPos: this.element.attributes.position,
                oldParent: this.element.relationships.parent.data.id,
                newParent: this.element.newParentId,
                sortArray: this.element.sortArray,
            };
            this.keyboardSelected = false;

            if (data.newParent === undefined || data.newPos === undefined) {
                const assistiveLive = this.$gettextInterpolate(
                    this.$gettext('%{elementTitle}. Neuordnung nicht möglich.'),
                    { elementTitle: this.element.attributes.title }
                );
                this.setAssistiveLiveContents(assistiveLive);
                return;
            }

            if (data.oldParent === data.newParent && data.oldPos === data.newPos) {
                const assistiveLive = this.$gettextInterpolate(
                    this.$gettext('%{elementTitle}. Neuordnung abgebrochen.'),
                    { elementTitle: this.element.attributes.title }
                );
                this.setAssistiveLiveContents(assistiveLive);
                return;
            }
            this.$emit('sort', data);
            const assistiveLive = this.$gettextInterpolate(
                this.$gettext('%{elementTitle}, abgelegt. Entgültige Position in der Liste: %{pos} von %{listLength}.'),
                { elementTitle: this.element.attributes.title, pos: data.newPos + 1, listLength: this.siblingCount }
            );
            this.setAssistiveLiveContents(assistiveLive);
        },
    },
    mounted() {
        if (this.element.relationships?.task?.data) {
            this.loadTask({
                taskId: this.element.relationships.task.data.id,
            });
        }
        if (this.newPos || this.newParentId) {
            this.keyboardSelected = true;
            this.$refs.sortableHandle.focus();
        }
    },
    watch: {
        newPos() {
            this.keyboardSelected = true;
            this.$refs.sortableHandle.focus();
        },
        newParentId() {
            this.keyboardSelected = true;
            this.$refs.sortableHandle.focus();
        },
    },
};
</script>
