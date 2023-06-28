<template>
    <div>
        <div v-if="!isSearching"
             class="studip-tree" :class="{'studip-tree-navigatable': showStructureAsNavigation}">
            <studip-progress-indicator v-if="isLoading" :size="48"></studip-progress-indicator>
            <studip-tree-list v-if="viewType === 'list' && startNode"  :with-children="withChildren"
                              :visible-children-only="visibleChildrenOnly"
                              :with-courses="withCourses" :semester="semester" :sem-class="semClass" :node="startNode"
                              :breadcrumb-icon="breadcrumbIcon" :editable="editable" :edit-url="editUrl"
                              :create-url="createUrl" :delete-url="deleteUrl" :with-export="withExport"
                              :show-structure-as-navigation="showStructureAsNavigation" :assignable="assignable"
                              :with-course-assign="withCourseAssign"
                              @change-current-node="changeCurrentNode"></studip-tree-list>
            <studip-tree-table v-else-if="viewType === 'table' && startNode" :with-children="withChildren"
                               :visible-children-only="visibleChildrenOnly"
                               :with-courses="withCourses" :semester="semester" :sem-class="semClass" :node="startNode"
                               :breadcrumb-icon="breadcrumbIcon" :editable="editable" :edit-url="editUrl"
                               :create-url="createUrl" :delete-url="deleteUrl" :with-export="withExport"
                               :show-structure-as-navigation="showStructureAsNavigation" :assignable="assignable"
                               :with-course-assign="withCourseAssign"
                               @change-current-node="changeCurrentNode"></studip-tree-table>
            <studip-tree-node v-else-if="viewType === 'tree' && startNode" :with-info="withInfo"
                              :visible-children-only="visibleChildrenOnly" :node="startNode"
                              :open-levels="openLevels" :openNodes="openNodes" :breadcrumb-icon="breadcrumbIcon"
                              :editable="editable" :edit-url="editUrl" :create-url="createUrl" :delete-url="deleteUrl"
                              :assignable="assignable" :assign-leaves-only="assignLeavesOnly"
                              :not-assignable-nodes="notAssignableNodes"></studip-tree-node>

        </div>
        <div v-else class="studip-tree">
            <tree-search-result :search-config="searchConfig"></tree-search-result>
        </div>
        <MountingPortal v-if="withSearch" mountTo="#search-widget" name="sidebar-search">
            <search-widget></search-widget>
        </MountingPortal>
    </div>
</template>

<script>
import axios from 'axios';
import { TreeMixin } from '../../mixins/TreeMixin';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';
import SearchWidget from '../SearchWidget.vue';
import StudipTreeList from './StudipTreeList.vue';
import StudipTreeTable from './StudipTreeTable.vue';
import StudipTreeNode from './StudipTreeNode.vue';
import TreeSearchResult from './TreeSearchResult.vue';

export default {
    name: 'StudipTree',
    components: {
        TreeSearchResult, SearchWidget, StudipProgressIndicator, StudipTreeList, StudipTreeTable, StudipTreeNode
    },
    mixins: [ TreeMixin ],
    props: {
        viewType: {
            type: String,
            default: 'tree'
        },
        treeId: {
            type: String,
            default: ''
        },
        startId: {
            type: String,
            required: true
        },
        title: {
            type: String,
            default: ''
        },
        openNodes: {
            type: Array,
            default: () => []
        },
        openLevels: {
            type: Number,
            default: 0
        },
        withChildren: {
            type: Boolean,
            default: true
        },
        withInfo: {
            type: Boolean,
            default: true
        },
        visibleChildrenOnly: {
            type: Boolean,
            default: true
        },
        withCourses: {
            type: Boolean,
            default: false
        },
        semester: {
            type: String,
            default: ''
        },
        semClass: {
            type: Number,
            default: 0
        },
        breadcrumbIcon: {
            type: String,
            default: 'literature'
        },
        itemIcon: {
            type: String,
            default: 'literature'
        },
        withSearch: {
            type: Boolean,
            default: false
        },
        withExport: {
            type: Boolean,
            default: false
        },
        withCourseAssign: {
            type: Boolean,
            default: false
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
        showStructureAsNavigation: {
            type: Boolean,
            default: false
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
        }
    },
    data() {
        return {
            nodeId: this.startId,
            startNode: null,
            currentNode: this.startNode,
            loaded: false,
            isLoading: false,
            showStructuralNavigation: false,
            searchConfig: {},
            isSearching: false
        }
    },
    methods: {
        changeCurrentNode(node) {
            this.currentNode = node;
            this.$nextTick(() => {
                document.getElementById('tree-breadcrumb-' + node.attributes.id)?.focus();
            });
        },
        exportUrl() {
            return STUDIP.URLHelper.getURL('dispatch.php/tree/export_csv');
        }
    },
    mounted() {
        window.focus();

        const loadingIndicator = axios.interceptors.request.use(config => {
            setTimeout(() => {
                if (!this.loaded) {
                    this.isLoading = true;
                }
            }, this.showProgressIndicatorTimeout);
            return config;
        });

        this.getNode(this.startId).then(response => {
            this.startNode = response.data.data;
            this.currentNode = this.startNode;
            this.loaded = true;
            this.isLoading = false;
        });

        axios.interceptors.request.eject(loadingIndicator);

        this.globalOn('do-search', searchterm => {
            this.searchConfig.searchterm = searchterm;
            this.searchConfig.semester = this.semester;
            this.searchConfig.classname = this.startNode.attributes.classname;
            this.isSearching = true;
        });

        this.globalOn('cancel-search', () => {
            this.searchConfig = {};
            this.isSearching = false;
        });
    }
}
</script>
