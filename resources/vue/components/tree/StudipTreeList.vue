<template>
    <article class="studip-tree-list">
        <header>
            <tree-breadcrumb v-if="currentNode.id !== 'root'" :node="currentNode"
                             :edit-url="editUrl" :icon="breadcrumbIcon" :assignable="assignable"
                             :num-children="children.length" :num-courses="courses.length"
                             :show-navigation="showStructureAsNavigation"
                             :visible-children-only="visibleChildrenOnly"></tree-breadcrumb>
        </header>
        <studip-progress-indicator v-if="isLoading"></studip-progress-indicator>
        <section v-else>
            <h1>
                {{ currentNode.attributes.name }}

                <a v-if="editable && currentNode.attributes.id !== 'root'"
                   :href="editUrl + '/' + currentNode.attributes.id"
                   @click.prevent="editNode(editUrl, currentNode.id)" data-dialog="size=medium"
                   :title="$gettextInterpolate($gettext('%{name} bearbeiten'), {name: currentNode.attributes.name})">
                    <studip-icon shape="edit" :size="20"></studip-icon>
                </a>

            </h1>
            <p v-if="currentNode.attributes.description?.trim() !== ''" class="studip-tree-node-info"
               v-html="currentNode.attributes['description-formatted']">
            </p>
        </section>

        <span aria-live="assertive" class="sr-only">{{ assistiveLive }}</span>

        <nav v-if="withChildren && currentNode.attributes['has-children']" >
            <h1>
                {{ $gettext('Unterebenen') }}
            </h1>
            <draggable v-model="children" handle=".drag-handle" :animation="300" tag="ul"
                       class="studip-tree-children" @end="dropChild">
                <li v-for="(child, index) in children" :key="index" class="studip-tree-child">
                    <a v-if="editable && children.length > 1" class="drag-link"
                       tabindex="0"
                       :title="$gettextInterpolate($gettext('Sortierelement für Element %{node}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.'), {node: child.attributes.name})"
                       @keydown="keyHandler($event, index)"
                       :ref="'draghandle-' + index">
                        <span class="drag-handle"></span>
                    </a>
                    <tree-node-tile :node="child" :semester="withCourses ? semester : 'all'" :sem-class="semClass"
                                    :url="nodeUrl(child.id, semester !== 'all' ? semester : null)"></tree-node-tile>
                </li>
            </draggable>
        </nav>
        <section v-else-if="withChildren && !currentNode.attributes['has-children']"  class="studip-tree-node-no-children">
            {{ $gettext('Auf dieser Ebene existieren keine weiteren Unterebenen.') }}
        </section>
        <section v-if="withCourses && thisLevelCourses === 0" class="studip-tree-node-no-courses">
            {{ $gettext('Auf dieser Ebene sind keine Veranstaltungen zugeordnet.')}}
        </section>

        <section v-if="thisLevelCourses + subLevelsCourses > 0" class="levels-actions">
            <span v-if="withCourses && showingAllCourses">
                <button type="button" @click="showAllCourses(false)"
                        :title="$gettext('Veranstaltungen auf dieser Ebene anzeigen')">
                    {{ $gettext('Veranstaltungen auf dieser Ebene anzeigen') }}
                </button>
            </span>
            <span v-if="withCourses && subLevelsCourses > 0 && !showingAllCourses">
                <button type="button" @click="showAllCourses(true)"
                        :title="$gettext('Veranstaltungen auf Unterebenen anzeigen')">
                    {{ $gettext('Veranstaltungen auf Unterebenen anzeigen') }}
                </button>
            </span>
        </section>
        <table v-if="courses.length > 0" class="default">
            <caption>{{ $gettext('Veranstaltungen') }}</caption>
            <colgroup>
                <col>
                <col>
            </colgroup>
            <thead>
                <tr v-if="totalCourseCount > limit">
                    <td colspan="2">
                        <studip-pagination :items-per-page="limit"
                                           :total-items="totalCourseCount"
                                           :current-offset="offset"
                                           @updateOffset="updateOffset"
                        />
                    </td>
                </tr>
                <tr>
                    <th>{{ $gettext('Name') }}</th>
                    <th>{{ $gettext('Information') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(course) in courses" :key="course.id" class="studip-tree-child studip-tree-course">
                    <td>
                        <a :href="courseUrl(course.id)"
                           :title="$gettextInterpolate($gettext('Zur Veranstaltung %{ course }'),
                                { course: course.attributes.title })">
                            <studip-icon shape="seminar" :size="26"></studip-icon>
                            <template v-if="course.attributes['course-number']">
                                {{ course.attributes['course-number'] }}
                            </template>
                            {{ course.attributes.title }}
                        </a>
                        <div :id="'course-dates-' + course.id" class="course-dates"></div>
                    </td>
                    <td>
                        <tree-course-details :course="course.id"></tree-course-details>
                    </td>
                </tr>
            </tbody>
            <tfoot v-if="totalCourseCount > limit">
                <tr>
                    <td colspan="2">
                        <studip-pagination :items-per-page="limit"
                                           :total-items="totalCourseCount"
                                           :current-offset="offset"
                                           @updateOffset="updateOffset"
                        />
                    </td>
                </tr>
            </tfoot>
        </table>
        <MountingPortal v-if="showExport" mountTo="#export-widget" name="sidebar-export">
            <tree-export-widget v-if="courses.length > 0"
                                :title="$gettext('Veranstaltungen exportieren')" :url="exportUrl()"
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
import TreeNodeTile from './TreeNodeTile.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';
import TreeCourseDetails from './TreeCourseDetails.vue';
import AssignLinkWidget from './AssignLinkWidget.vue';
import StudipPagination from '../StudipPagination.vue';

export default {
    name: 'StudipTreeList',
    components: {
        draggable, StudipProgressIndicator, TreeExportWidget, TreeBreadcrumb, TreeNodeTile, TreeCourseDetails,
        AssignLinkWidget, StudipPagination
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
    computed: {
        showExport() {
            return this.withExport && document.getElementById('export-widget');
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
                this.getNodeCourses(node, this.offset, this.semester, this.semClass, '', false)
                    .then(courses => {
                        this.totalCourseCount = courses.data.meta.page.total;
                        this.offset = Math.ceil(courses.data.meta.page.offset / this.limit);
                        this.courses = courses.data.data;
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
            this.getNodeCourses(this.currentNode, this.offset, this.semester, this.semClass, '', state)
                .then(courses => {
                    this.totalCourseCount = courses.data.meta.page.total;
                    this.offset = Math.ceil(courses.data.meta.page.offset / this.limit);
                    this.courses = courses.data.data;
                    this.showingAllCourses = state;
                });
        }
    },
    mounted() {
        if (this.withChildren) {
            this.getNodeChildren(this.currentNode, this.visibleChildrenOnly).then(response => {
                this.children = response.data.data;
            });
        }

        this.getNodeCourseInfo(this.currentNode, this.semester, this.semClass)
            .then(response => {
                this.thisLevelCourses = response?.data.courses;
                this.subLevelsCourses = response?.data.allCourses;
            });

        if (this.withCourses) {
            this.getNodeCourses(this.currentNode, 0, this.semester, this.semClass)
                .then(courses => {
                    this.totalCourseCount = courses.data.meta.page.total;
                    this.offset = 0;
                    this.courses = courses.data.data;
                });
        }

        this.globalOn('open-tree-node', node => {
            this.openNode(node);
        });

        this.globalOn('load-tree-node', id => {
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
<style scoped>
.levels-actions > span:not(:first-child)::before {
    content: ' | ';
}
</style>
