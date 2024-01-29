<template>
    <section class="studip-tree-node">
        <span :class="{ 'studip-tree-node-content': true, 'studip-tree-node-active': node?.id === activeNode?.id }">
            <a @click.prevent="toggleNode(true)">
                <div v-if="node.attributes['has-children']" class="studip-tree-node-toggle">
                    <studip-icon :shape="openState ? 'arr_1down': 'arr_1right'" :size="20"/>
                </div>
            </a>
            <button v-if="isAssignable && node.attributes.id !== 'root'" class="studip-tree-node-assignment-state"
                    @click.prevent="changeAssignmentState()" :title="$gettext('Zuordnung ändern')">
                <studip-icon :shape="assignmentState === 0
                    ? 'checkbox-unchecked'
                    : (assignmentState === 1 ? 'checkbox-checked' : 'checkbox-indeterminate')"></studip-icon>
            </button>
            <a @click.prevent="toggleNode(true)">
                <div class="studip-tree-node-name">
                    {{ node.attributes.name }}
                </div>
            </a>
            <studip-tooltip-icon v-if="withInfo && !isLoading && node.attributes.description?.trim() !== ''"
                                 :text="node.attributes['description-formatted'].trim()"></studip-tooltip-icon>
            <input v-if="isAssignable && node.attributes.id !== 'root'" type="hidden" :name="assignmentAction"
                   :value="node.attributes.id">
            <a v-if="editable && node.attributes.id !== 'root'" :href="editUrl + '/' + node.attributes.id"
               @click.prevent="editNode(editUrl, node.id)" data-dialog="size=medium"
               class="studip-tree-node-edit-link">
                <studip-icon shape="edit"></studip-icon>
            </a>
        </span>
        <div v-if="isLoading" class="studip-spinner">
            <studip-asset-img file="ajax-indicator-black.svg" width="20"/>
            {{ $gettext('Daten werden geladen...' )}}
        </div>
        <ul v-if="node.attributes['has-children'] && openState" class="studip-tree-children">
            <li v-for="(child) in children" :key="child.id" >
                <studip-tree-node :node="child" :editable="editable" :edit-url="editUrl" :create-url="createUrl"
                                  :delete-url="deleteUrl" :assignable="assignable" :ancestors="theAncestors"
                                  :not-assignable-nodes="notAssignableNodes" :open-nodes="openNodes"
                                  :open-levels="openLevels > 0 ? (openLevels - 1) : 0"
                                  :visible-children-only="visibleChildrenOnly"
                                  :active-node="activeNode" :with-info="withInfo"></studip-tree-node>
            </li>
        </ul>
    </section>
</template>

<script>
import { TreeMixin } from '../../mixins/TreeMixin';
import StudipIcon from '../StudipIcon.vue';
import StudipAssetImg from '../StudipAssetImg.vue';
import axios from 'axios';
import StudipTooltipIcon from '../StudipTooltipIcon.vue';

export default {
    name: 'StudipTreeNode',
    components: { StudipTooltipIcon, StudipAssetImg, StudipIcon },
    mixins: [ TreeMixin ],
    props: {
        node: {
            type: Object,
            required: true
        },
        activeNode: {
            type: Object,
            default: null
        },
        isOpen: {
            type: Boolean,
            default: false
        },
        breadcrumbIcon: {
            type: String,
            default: 'literature'
        },
        withInfo: {
            type: Boolean,
            default: true
        },
        editable: {
            type: Boolean,
            default: false
        },
        editUrl: {
            type: String,
            default: ''
        },
        createUrl: {
            type: String,
            default: ''
        },
        deleteUrl: {
            type: String,
            default: ''
        },
        visibleChildrenOnly: {
            type: Boolean,
            default: true
        },
        withCourses: {
            type: Boolean,
            default: true
        },
        assignable: {
            type: Boolean,
            default: false
        },
        assignLeavesOnly: {
            type: Boolean,
            default: false
        },
        notAssignableNodes: {
            type: Array,
            default: () => []
        },
        openLevels: {
            type: Number,
            default: 0
        },
        openNodes: {
            type: Array,
            default: () => []
        },
        ancestors: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            isLoading: false,
            childrenLoaded: false,
            children: [],
            semester: 'all',
            openState: this.isOpen,
            theAncestors: this.ancestors,
            assignedCourses: 0,
            assignmentState: 0,
            assignmentAction: ''
        }
    },
    methods: {
        toggleNode(emitEvent = false) {
            this.courses = [];
            this.openState = !this.openState;
            if (emitEvent) {
                STUDIP.eventBus.emit('load-tree-node', this.node.id);
            }
            if (!this.childrenLoaded) {
                this.children = [];
                const loadingIndicator = axios.interceptors.request.use(config => {
                    setTimeout(() => {
                        if (!this.childrenLoaded) {
                            this.isLoading = true;
                        }
                    }, 500);
                    return config;
                });
                this.getNodeChildren(this.node, this.visibleChildrenOnly)
                    .then(response => {
                        this.isLoading = false;
                        this.children = response.data.data;
                        this.childrenLoaded = true;
                    });
                axios.interceptors.request.eject(loadingIndicator);
            }
        },
        /**
         * Check whether currently selected course are assigned to this node.
         */
        checkAssignments() {
            const courses = document.querySelectorAll('table.selected-courses input[name="courses[]"]') ?? [];
            let ids = [];
            for (const course of courses) {
                ids.push(course.value);
            }

            if (ids.length > 0) {
                this.getNodeCourses(this.node, 0, 'all', 0, '', false, ids)
                    .then(response => {
                        // None of the given courses are assigned here.
                        if (response.data.data.length === 0) {
                            this.assignedCourses = this.assignmentState = 0;
                        // All of the given courses are assigned here.
                        } else if (response.data.data.length === ids.length) {
                            this.assignedCourses = this.assignmentState = 1;
                        // Some of the given courses are assigned here.
                        } else {
                            this.assignedCourses = this.assignmentState = -1;
                        }
                    });
            }
        },
        /**
         * Change what shall be done on submitting the form.
         */
        changeAssignmentState() {
            // Current state is 0 -> remove all assignments.
            if (this.assignmentState === 0) {
                // Not all courses are assigned here -> next state is indeterminate.
                if (this.assignedCourses === -1) {
                    this.assignmentState = -1;
                // Next state is 1 -> add assignments here.
                } else {
                    this.assignmentState = 1;
                }
            // Current state is 1 -> next state is 0 -> remove assignments here.
            } else if (this.assignmentState === 1) {
                this.assignmentState = 0;
            // Current state is indeterminate -> next state is 1 -> add assignments here.
            } else {
                this.assignmentState = 1;
            }

            // Current state returned to original, nothing needs to be done.
            if (this.assignmentState === this.assignedCourses) {
                this.assignmentAction = '';
            // Current state is different from original state -> add or remove.
            } else {
                switch (this.assignmentState) {
                    case 0:
                        this.assignmentAction = 'delete_assignments[]';
                        break;
                    case 1:
                        this.assignmentAction = 'add_assignments[]';
                        break;
                }
            }

        }
    },
    computed: {
        isAssignable() {
            return this.assignable
                && this.node.attributes.assignable
                && !this.notAssignableNodes?.includes(this.node.id);
        }
    },
    mounted() {
        if (this.openLevels > 0) {
            this.toggleNode();
        }

        if (this.ancestors.length === 0) {
            for (const open of this.openNodes) {
                this.getNode(open).then((response) => {
                    const haystack = response.data.data.attributes.ancestors?.map(element => {
                        return element.classname + '_' + element.id;
                    });
                    if (haystack) {
                        this.theAncestors = haystack;
                        if (this.theAncestors.includes(this.node.id)) {
                            this.toggleNode();
                        }
                    }
                });

            }
        }

        this.globalOn('sort-tree-children', data => {
            if (this.node.id === data.parent) {
                this.children = data.children;
            }
        });

        this.$nextTick(() => {
            if (this.theAncestors?.includes(this.node.id) && !this.openState) {
                this.toggleNode();
            }
            if (this.isAssignable && this.node.attributes.id !== 'root') {
                this.checkAssignments();
            }
        });
    },
    beforeDestroy() {
        STUDIP.eventBus.off('sort-tree-children');
    }
}
</script>
