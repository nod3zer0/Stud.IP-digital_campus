<template>
    <li v-if="showItem">
        <div
            :class="[
                isRoot ? 'cw-tree-item-is-root' : '',
                isFirstLevel ? 'cw-tree-item-first-level' : '',
                hasPurposeClass ? 'cw-tree-item-' + purposeClass : '',
            ]"
        >
            <router-link
                :to="'/structural_element/' + element.id"
                class="cw-tree-item-link"
                :class="{ 'cw-tree-item-link-current': isCurrent }"
            >
                {{ element.attributes.title || "–" }}
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
            </router-link>
        </div>
        <ul
            v-if="hasChildren"
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
        </ul>
    </li>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

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
            context: 'context',
            taskById: 'courseware-tasks/byId',
            userById: 'users/byId',
            groupById: 'status-groups/byId',
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
        hasReleaseOrWithdrawDate() {
            return (
                this.element.attributes['release-date'] !== null || this.element.attributes['withdraw-date'] !== null
            );
        },
        hasWriteApproval() {
            const writeApproval = this.element.attributes['write-approval'];

            if (Object.keys(writeApproval).length === 0) {
                return false;
            }
            return (writeApproval.all || writeApproval.groups.length > 0 || writeApproval.users.length > 0) && this.element.attributes['can-edit'];
        },
        hasNoReadApproval() {
            if (this.context.type === 'users') {
                return false;
            }
            const readApproval = this.element.attributes['read-approval'];

            if (Object.keys(readApproval).length === 0 || this.hasWriteApproval) {
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
                (this.context.type === 'courses' && this.element.attributes.purpose === 'task')
            ) {
                return this.element.attributes.purpose;
            }
            return '';
        },
        task() {
            if (this.element.relationships.task.data) {
                return this.taskById({
                    id: this.element.relationships.task.data.id,
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
            return this.element.attributes.purpose === 'task';
        },
        showItem() {
            if (this.isTask) {
                return this.task !== undefined;
            }

            return true;
        }
    },
    methods: {
        ...mapActions({
            loadTask: 'loadTask',
        }),
    },
    mounted() {
        if (this.element.relationships.task.data) {
            this.loadTask({
                taskId: this.element.relationships.task.data.id,
            });
        }
    },
};
</script>
