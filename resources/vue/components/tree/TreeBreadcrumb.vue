<template>
    <div class="studip-tree-breadcrumb contentbar">
        <nav class="contentbar-nav"></nav>
        <div :class="{'contentbar-wrapper-left': true, 'with-navigation': showNavigation, 'editable': editable,
                'with-navigation-and-editable': showNavigation && editable}">
            <studip-icon :shape="icon" :size="24"></studip-icon>
            <nav v-if="node.attributes.ancestors" class="studip-tree-breadcrumb-list contentbar-nav">
                <span v-for="(ancestor, index) in node.attributes.ancestors"
                          :key="ancestor.id">
                    <a :href="nodeUrl(ancestor.classname + '_' + ancestor.id)" :ref="ancestor.id"
                       @click.prevent="openNode(ancestor.id, ancestor.classname)" tabindex="0"
                       :id="'tree-breadcrumb-' + ancestor.id"
                       :title="$gettextInterpolate($gettext('%{ node } öffnen'), { node: ancestor.name})">
                        {{ ancestor.name }}
                    </a>
                    <template v-if="index !== node.attributes.ancestors.length - 1">
                        /
                    </template>
                </span>
            </nav>
        </div>
        <div class="contentbar-wrapper-right">
            <div v-if="showNavigation" class="studip-tree-navigation-wrapper">
                <button type="button" tabindex="0"
                        :title="navigationOpen ? $gettext('Navigation schließen') : $gettext('Navigation öffnen')"
                        @click.prevent="toggleNavigation" :aria-expanded="navigationOpen">
                    <studip-icon shape="table-of-contents" :size="24"></studip-icon>
                </button>
                <article class="studip-tree-navigation" v-if="navigationOpen">
                    <header>
                        <h1>{{ $gettext('Inhalt') }}</h1>
                        <button type="button" tabindex="0"
                                @click.prevent="toggleNavigation">
                            <studip-icon shape="decline" :size="24"></studip-icon>
                        </button>
                    </header>
                    <studip-tree-node :with-info="false" :node="rootNode" :active-node="node" :open-nodes="[ node.id ]"
                                      :visible-children-only="visibleChildrenOnly"></studip-tree-node>
                </article>
            </div>
            <button v-if="assignable" type="submit" class="assign-button"
                    :title="$gettext('Diesen Eintrag zuweisen')">
                <studip-icon shape="arr_2right" :size="20"></studip-icon>
            </button>
            <studip-action-menu v-if="editable" :items="actionMenuItems()"
                                @add-tree-node="addNode" @edit-tree-node="editNode" @delete-tree-node="deleteNode"/>
        </div>
    </div>
</template>

<script>
import { TreeMixin } from '../../mixins/TreeMixin';
import StudipIcon from '../StudipIcon.vue';
import StudipTreeNode from './StudipTreeNode.vue';
import axios from 'axios';

export default {
    name: 'TreeBreadcrumb',
    components: { StudipIcon, StudipTreeNode },
    mixins: [ TreeMixin ],
    props: {
        node: {
            type: Object,
            required: true
        },
        icon: {
            type: String,
            required: true
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
        showNavigation: {
            type: Boolean,
            default: false
        },
        assignable: {
            type: Boolean,
            default: false
        },
        numChildren: {
            type: Number,
            default: 0
        },
        numCourses: {
            type: Number,
            default: 0
        },
        visibleChildrenOnly: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            navigationOpen: false,
            rootNode: null
        }
    },
    methods: {
        openNode(id, classname) {
            STUDIP.eventBus.emit('load-tree-node', classname + '_' + id);
            this.$refs[id][0].focus();
        },
        actionMenuItems() {
            let entries = [];

            if (this.editable && this.createUrl !== '') {
                entries.push({
                    id: 'create',
                    label: this.$gettext('Neues Unterelement anlegen'),
                    icon: 'add',
                    emit: 'add-tree-node',
                    emitArguments: this.node
                });
            }

            if (this.editable && this.node.attributes.id !== 'root') {
                entries.push({
                    id: 'edit',
                    label: this.$gettext('Dieses Element bearbeiten'),
                    icon: 'edit',
                    emit: 'edit-tree-node',
                    emitArguments: this.node
                });
                entries.push({
                    id: 'delete',
                    label: this.$gettext('Dieses Element löschen'),
                    icon: 'trash',
                    emit: 'delete-tree-node',
                    emitArguments: this.node
                });
            }

            return entries;
        },
        toggleNavigation() {
            this.navigationOpen = !this.navigationOpen;
        },
        addNode(parent) {
            STUDIP.Dialog.fromURL(this.createUrl + '/' + parent.id, { data: { from: this.nodeUrl(parent.id) }});
        },
        editNode(node) {
            STUDIP.Dialog.fromURL(this.editUrl + '/' + node.id, { data: { from: this.nodeUrl(node.id) }});
        },
        deleteNode(node) {
            let text = this.$gettext('Sind sie sicher, dass der Eintrag "%{ node }" gelöscht werden soll?');
            let context = {
                node: node.attributes.name
            };

            if (this.numChildren > 0 && this.numCourses === 0) {
                text = this.$gettext('Sind sie sicher, dass der Eintrag "%{ node }" gelöscht werden soll? Er hat %{ children } Unterelemente.');
                context.children = this.numChildren;
            } else if (this.numChildren === 0 && this.numCourses > 0) {
                text = this.$gettext('Sind sie sicher, dass der Eintrag "%{ node }" gelöscht werden soll? Er hat %{ courses } Veranstaltungszuordnungen.');
                context.courses = this.numCourses;
            } else if (this.numChildren > 0 && this.numCourses > 0) {
                text = this.$gettext('Sind sie sicher, dass der Eintrag "%{ node }" gelöscht werden soll? Er hat %{ children } Unterelemente und %{ courses } Veranstaltungszuordnungen.');
                context.children = this.numChildren;
                context.courses = this.numCourses;
            }

            STUDIP.Dialog.confirm(
                this.$gettextInterpolate(text, context)
            ).done(() => {
                axios.post(this.deleteUrl + '/' + node.id).then(() => {
                    const parent = node.attributes.ancestors[node.attributes.ancestors.length - 2];
                    window.location = this.nodeUrl(parent.classname + '_' + parent.id);
                });
            });
        }
    },
    mounted() {
        const root = this.node.attributes.ancestors[0];
        this.getNode(root.classname + '_' + root.id).then(response => {
            this.rootNode = response.data.data;
        });
    }
}
</script>
