<template>
    <div v-if="isLoading">
        <studip-progress-indicator></studip-progress-indicator>
    </div>
    <article v-else class="studip-tree-table">
        <header>
            <tree-breadcrumb v-if="currentNode.id !== 'root'" :node="currentNode"
                             :icon="breadcrumbIcon" :editable="editable" :edit-url="editUrl" :create-url="createUrl"
                             :delete-url="deleteUrl" :show-navigation="showStructureAsNavigation"
                             :num-children="children.length" :num-courses="courses.length"
                             :assignable="assignable" :visible-children-only="visibleChildrenOnly"></tree-breadcrumb>
        </header>
        <section v-if="withChildren && !currentNode.attributes['has-children']" class="studip-tree-node-no-children">
            {{ $gettext('Auf dieser Ebene existieren keine weiteren Unterebenen.')}}
        </section>

        <span aria-live="assertive" class="sr-only">{{ assistiveLive }}</span>

        <div v-if="currentNode.attributes.description?.trim() !== ''"
             v-html="currentNode.attributes['description-formatted']"></div>

        <section v-if="thisLevelCourses === 0" class="studip-tree-node-no-courses">
            {{ $gettext('Auf dieser Ebene sind keine Veranstaltungen zugeordnet.')}}
        </section>

        <section v-if="thisLevelCourses + subLevelsCourses > 0">
            <span v-if="withCourses && showingAllCourses">
                <button type="button" @click="showAllCourses(false)"
                        :title="$gettext('Veranstaltungen auf dieser Ebene anzeigen')">
                    {{ $gettext('Veranstaltungen auf dieser Ebene anzeigen') }}
                </button>
            </span>
            <template v-if="thisLevelCourses > 0 && subLevelsCourses > 0">
                |
            </template>
            <span v-if="withCourses && subLevelsCourses > 0 && !showingAllCourses">
                <button type="button" @click="showAllCourses(true)"
                        :title="$gettext('Veranstaltungen auf allen Unterebenen anzeigen')">
                    {{ $gettext('Veranstaltungen auf allen Unterebenen anzeigen') }}
                </button>
            </span>
        </section>

        <table v-if="currentNode.attributes['has-children'] || courses.length > 0" class="default">
            <caption class="studip-tree-node-info">
                <span v-if="withChildren && children.length > 0">
                    {{ $gettextInterpolate($gettext('%{ count } Unterebenen'), { count: children.length }) }}
                </span>
                <span v-if="withChildren && children.length > 0 && withCourses && courses.length > 0">
                    ,
                </span>
            </caption>
            <colgroup>
                <col style="width: 20px">
                <col style="width: 30px">
                <col>
                <col style="width: 40%">
            </colgroup>
            <thead>
                <tr>
                    <th></th>
                    <th>{{ $gettext('Typ') }}</th>
                    <th>{{ $gettext('Name') }}</th>
                    <th>{{ $gettext('Information') }}</th>
                </tr>
            </thead>
            <draggable v-model="children" handle=".drag-handle" :animation="300"
                       @end="dropChild" tag="tbody" role="listbox">
                <tr v-for="(child, index) in children" :key="index" class="studip-tree-child">
                    <td>
                        <a v-if="editable && children.length > 1" class="drag-link" role="option"
                           tabindex="0"
                           :title="$gettextInterpolate($gettext('Sortierelement für Element %{node}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.'), {node: child.attributes.name})"
                           @keydown="keyHandler($event, index)"
                           :ref="'draghandle-' + index">
                            <span class="drag-handle"></span>
                        </a>
                    </td>
                    <td>
                        <studip-icon :shape="child.attributes['has-children'] ? 'folder-full' : 'folder-empty'"
                                     :size="26"></studip-icon>
                    </td>
                    <td>
                        <a :href="nodeUrl(child.id, semester !== 'all' ? semester : null)" tabindex="0"
                           @click.prevent="openNode(child)"
                           :title="$gettextInterpolate($gettext('Unterebene %{ node } öffnen'),
                                { node: node.attributes.name })">
                            {{ child.attributes.name }}
                        </a>
                    </td>
                    <td>
                        <tree-node-course-info :node="child" :semester="semester"
                                               :sem-class="semClass"></tree-node-course-info>
                    </td>
                </tr>
                <tr v-for="(course) in courses" :key="course.id" class="studip-tree-child studip-tree-course">
                    <td></td>
                    <td>
                        <studip-icon shape="seminar" :size="26"></studip-icon>
                    </td>
                    <td>
                        <a :href="courseUrl(course.id)" tabindex="0"
                           :title="$gettextInterpolate($gettext('Zur Veranstaltung %{ course }'),
                                { course: course.attributes.title })">
                            <template v-if="course.attributes['course-number']">
                                {{ course.attributes['course-number'] }}
                            </template>
                            {{ course.attributes.title }}
                        </a>
                        <div :id="'course-dates-' + course.id" class="course-dates"></div>
                    </td>
                    <td :colspan="editable ? 2 : null">
                        <tree-course-details :course="course.id"></tree-course-details>
                    </td>
                </tr>
            </draggable>
        </table>
        <MountingPortal v-if="withExport" mountTo="#export-widget" name="sidebar-export">
            <tree-export-widget v-if="courses.length > 0" :title="$gettext('Download des Ergebnisses')" :url="exportUrl()"
                                :export-data="courses"></tree-export-widget>
        </MountingPortal>
        <MountingPortal v-if="withCourseAssign" mountTo="#assign-widget" name="sidebar-assign-courses">
            <assign-link-widget v-if="courses.length > 0" :node="currentNode" :courses="courses"></assign-link-widget>
        </MountingPortal>
    </article>
</template>

<script>
import draggable from 'vuedraggable';
import { TreeMixin } from '../../mixins/TreeMixin';
import TreeExportWidget from './TreeExportWidget.vue';
import TreeBreadcrumb from './TreeBreadcrumb.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';
import StudipIcon from '../StudipIcon.vue';
import TreeNodeCourseInfo from './TreeNodeCourseInfo.vue';
import TreeCourseDetails from "./TreeCourseDetails.vue";
import AssignLinkWidget from "./AssignLinkWidget.vue";

export default {
    name: 'StudipTreeTable',
    components: {
        draggable, TreeExportWidget, TreeCourseDetails, StudipIcon, StudipProgressIndicator, TreeBreadcrumb,
        TreeNodeCourseInfo, AssignLinkWidget
    },
    mixins: [ TreeMixin ],
    props: {
        node: {
            type: Object,
            required: true
        },
        breadcrumbIcon: {
            type: String,
            default: 'literature'
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
        withCourses: {
            type: Boolean,
            default: false
        },
        withExport: {
            type: Boolean,
            default: false
        },
        withChildren: {
            type: Boolean,
            default: true
        },
        visibleChildrenOnly: {
            type: Boolean,
            default: true
        },
        assignable: {
            type: Boolean,
            default: false
        },
        withCourseAssign: {
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
        showStructureAsNavigation: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            currentNode: this.node,
            isLoading: false,
            isLoaded: false,
            children: [],
            courses: [],
            assistiveLive: '',
            subLevelsCourses: 0,
            thisLevelCourses: 0,
            showingAllCourses: false
        }
    },
    methods: {
        openNode(node, pushState = true) {
            this.currentNode = node;
            this.$emit('change-current-node', node);

            if (this.withChildren) {
                this.getNodeChildren(node, this.visibleChildrenOnly).then(response => {
                    this.children = response.data.data;
                });
            }

            this.getNodeCourseInfo(node, this.semester, this.semClass)
                .then(response => {
                    this.thisLevelCourses = response?.data.courses;
                    this.subLevelsCourses = response?.data.allCourses;
                });

            if (this.withCourses) {

                this.getNodeCourses(node, this.semester, this.semClass, '', false)
                    .then(response => {
                        this.courses = response.data.data;
                    });
            }

            // Update browser history.
            if (pushState) {
                const nodeId = node.id;
                const url = STUDIP.URLHelper.getURL('', {node_id: nodeId});
                window.history.pushState({nodeId}, '', url);
            }

            // Update node_id for semester selector.
            const semesterSelector = document.querySelector('#semester-selector-node-id');
            semesterSelector.value = node.id;
        },
        dropChild() {
            this.updateSorting(this.currentNode.id, this.children);
        },
        keyHandler(e, index) {
            switch (e.keyCode) {
                case 38: // up
                    e.preventDefault();
                    this.decreasePosition(index);
                    this.$nextTick(() => {
                        this.$refs['draghandle-' + (index - 1)][0].focus();
                        this.assistiveLive = this.$gettextInterpolate(
                            this.$gettext('Aktuelle Position in der Liste: %{pos} von %{listLength}.'),
                            { pos: index, listLength: this.children.length }
                        );
                    });
                    break;
                case 40: // down
                    e.preventDefault();
                    this.increasePosition(index);
                    this.$nextTick(function () {
                        this.$refs['draghandle-' + (index + 1)][0].focus();
                        this.assistiveLive = this.$gettextInterpolate(
                            this.$gettext('Aktuelle Position in der Liste: %{pos} von %{listLength}.'),
                            { pos: index + 2, listLength: this.children.length }
                        );
                    });
                    break;
            }
        },
        decreasePosition(index) {
            if (index > 0) {
                const temp = this.children[index - 1];
                this.children[index - 1] = this.children[index];
                this.children[index] = temp;
                this.updateSorting(this.currentNode.id, this.children);
            }
        },
        increasePosition(index) {
            if (index < this.children.length) {
                const temp = this.children[index + 1];
                this.children[index + 1] = this.children[index];
                this.children[index] = temp;
                this.updateSorting(this.currentNode.id, this.children);
            }
        },
        showAllCourses(state) {
            this.getNodeCourses(this.currentNode, this.semester, this.semClass, '', state)
                .then(courses => {
                    this.courses = courses.data.data;
                    this.showingAllCourses = state;
                });
        }
    },
    mounted() {
        if (this.withChildren) {
            this.getNodeChildren(this.node, this.visibleChildrenOnly).then(response => {
                this.children = response.data.data;
            });
        }

        this.getNodeCourseInfo(this.currentNode, this.semester, this.semClass)
            .then(response => {
                this.thisLevelCourses = response?.data.courses;
                this.subLevelsCourses = response?.data.allCourses;
            });

        if (this.withCourses) {
            this.getNodeCourses(this.currentNode, this.semester, this.semClass)
                .then(courses => {
                    this.courses = courses.data.data;
                });
        }

        this.globalOn('open-tree-node', node => {
            STUDIP.eventBus.emit('cancel-search');
            this.openNode(node);
        });

        this.globalOn('load-tree-node', id => {
            STUDIP.eventBus.emit('cancel-search');
            this.getNode(id).then(response => {
                this.openNode(response.data.data);
            });
        });

        this.globalOn('sort-tree-children', data => {
            if (this.currentNode.id === data.parent) {
                this.children = data.children;
            }
        });

        window.addEventListener('popstate', (event) => {
            if (event.state) {
                if ('nodeId' in event.state) {
                    this.getNode(event.state.nodeId).then(response => {
                        this.openNode(response.data.data, false);
                    });
                }
            } else {
                this.openNode(this.node, false);
            }
        });

        // Add current node to semester selector widget.
        this.$nextTick(() => {
            const semesterForm = document.querySelector('#semester-selector .sidebar-widget-content form');
            const nodeField = document.createElement('input');
            nodeField.id = 'semester-selector-node-id';
            nodeField.type = 'hidden';
            nodeField.name = 'node_id';
            nodeField.value = this.node.id;
            semesterForm.appendChild(nodeField);
        });
    },
    beforeDestroy() {
        STUDIP.eventBus.off('open-tree-node');
        STUDIP.eventBus.off('load-tree-node');
        STUDIP.eventBus.off('sort-tree-children');
    }
}
</script>
