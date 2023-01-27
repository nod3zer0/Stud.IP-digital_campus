<template>
    <sidebar-widget :title="$gettext('Import')">
        <template #content>
            <ul class="widget-list widget-links cw-import-widget">
                <li class="cw-import-widget-archive">
                    <button @click="importElements">
                        {{ $gettext('Seiten importieren') }}
                    </button>
                </li>
                <li class="cw-import-widget-copy">
                    <button @click="copyElements">
                        {{ $gettext('Seiten kopieren') }}
                    </button>
                </li>
                <li v-if="inCourseContext && userIsTeacher" class="cw-action-widget-link">
                    <button @click="linkElement">
                        {{ $gettext('Seiten verknüpfen') }}
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
    name: 'courseware-import-widget',
    components: {
        SidebarWidget,
    },
    computed: {
        ...mapGetters({
            context: 'context',
            userIsTeacher: 'userIsTeacher',
        }),
        inCourseContext() {
            return this.context.type === 'courses';
        }
    },
    methods: {
        ...mapActions({
            showElementImportDialog: 'showElementImportDialog',
            showElementCopyDialog: 'showElementCopyDialog',
            showElementLinkDialog: 'showElementLinkDialog',
        }),
        importElements() {
            this.showElementImportDialog(true);
        },
        copyElements() {
            this.showElementCopyDialog(true);
        },
        linkElement() {
            this.showElementLinkDialog(true);
        },
    },
}
</script>