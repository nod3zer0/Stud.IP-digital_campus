<template>
    <sidebar-widget :title="$gettext('Import')">
        <template #content>
            <ul class="widget-list widget-links cw-import-widget">
                <li class="cw-import-widget-archive">
                    <button @click="setShowUnitImportDialog(true)">
                        {{ $gettext('Lernmaterial importieren') }}
                    </button>
                </li>
                <li class="cw-import-widget-copy">
                    <button @click="setShowUnitCopyDialog(true)">
                        {{ $gettext('Lernmaterial kopieren') }}
                    </button>
                </li>
            </ul>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../../SidebarWidget.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-shelf-import-widget',
    components: {
        SidebarWidget,
    },
    methods: {
        ...mapActions({
            setShowUnitCopyDialog: 'setShowUnitCopyDialog',
            setShowUnitImportDialog: 'setShowUnitImportDialog',
        }),
    }
}
</script>