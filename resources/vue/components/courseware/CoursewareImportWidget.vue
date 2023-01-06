<template>
    <sidebar-widget :title="$gettext('Import')">
        <template #content>
            <ul class="widget-list widget-links cw-import-widget">
                <li class="cw-import-widget-archive">
                    <button @click="importElements">
                        {{ $gettext('Lerninhalte importieren') }}
                    </button>
                </li>
                <li class="cw-import-widget-copy">
                    <button @click="copyElements">
                        {{ $gettext('Lerninhalte kopieren') }}
                    </button>
                </li>
            </ul>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-import-widget',
    components: {
        SidebarWidget,
    },
    methods: {
        ...mapActions({
            showElementImportDialog: 'showElementImportDialog',
            showElementCopyDialog: 'showElementCopyDialog'
        }),
        importElements() {
            this.showElementImportDialog(true);
        },
        copyElements() {
            this.showElementCopyDialog(true);
        },
    },
}
</script>