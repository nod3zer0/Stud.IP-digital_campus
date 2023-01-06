<template>
    <sidebar-widget :title="$gettext('Aktionen')" v-if="structuralElement">
        <template #content>
            <ul class="widget-list widget-links cw-action-widget">
                <li v-if="canEdit" class="cw-action-widget-add">
                    <button @click="addElement">
                        {{ $gettext('Seite hinzufügen') }}
                    </button>
                </li>
                <li v-if="inCourseContext && userIsTeacher" class="cw-action-widget-link">
                    <button @click="linkElement">
                        {{ $gettext('Seite verknüpfen') }}
                    </button>
                </li>
            </ul>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-action-widget',
    props: ['structuralElement'],
    components: {
        SidebarWidget,
    },
    computed: {
        ...mapGetters({
            context: 'context',
            userIsTeacher: 'userIsTeacher',
        }),
        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
        },
        currentId() {
            return this.structuralElement?.id;
        },
        inCourseContext() {
            return this.context.type === 'courses';
        }
    },
    methods: {
        ...mapActions({
            showElementAddDialog: 'showElementAddDialog',
            showElementLinkDialog: 'showElementLinkDialog',
        }),
        addElement() {
            this.showElementAddDialog(true);
        },
        linkElement() {
            this.showElementLinkDialog(true);
        },
    },
};
</script>
