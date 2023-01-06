<template>
    <sidebar-widget :title="$gettext('Ansichten')" v-if="structuralElement">
        <template #content>
            <ul class="widget-list widget-links sidebar-views cw-view-widget">
                <li :class="{ active: readView }">
                    <button @click="setReadView">
                        <translate>Lesen</translate>
                    </button>
                </li>
                <li
                    v-if="canEdit"
                    :class="{ active: editView }"
                >
                    <button @click="setEditView">
                        <translate>Bearbeiten</translate>
                    </button>
                </li>
                <li
                    v-if="context.type === 'courses' && canVisit"
                    :class="{ active: discussView }"
                >
                    <button @click="setDiscussView">
                        <translate>Kommentieren</translate>
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
    name: 'courseware-view-widget',
    props: ['structuralElement', 'canVisit'],
    components: {
        SidebarWidget
    },
    computed: {
        ...mapGetters({
            viewMode: 'viewMode',
            context: 'context',
        }),
        readView() {
            return this.viewMode === 'read';
        },
        editView() {
            return this.viewMode === 'edit';
        },
        discussView() {
            return this.viewMode === 'discuss';
        },
        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
        },
    },
    methods: {
        ...mapActions({
            coursewareViewMode: 'coursewareViewMode',
            coursewareBlockAdder: 'coursewareBlockAdder',
            setToolbarItem: 'coursewareSelectedToolbarItem',
        }),
        setReadView() {
            this.coursewareViewMode('read');
            this.setToolbarItem('contents');
            this.coursewareBlockAdder({});
        },
        setEditView() {
            this.coursewareViewMode('edit');
        },
        setDiscussView() {
            this.coursewareViewMode('discuss');
        },
    },
};
</script>
