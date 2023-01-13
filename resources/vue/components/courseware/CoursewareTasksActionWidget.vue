<template>
    <sidebar-widget id="courseware-action-widget" :title="$gettext('Aktionen')">
        <template #content>
            <ul class="widget-list widget-links cw-action-widget">
                <li class="cw-action-widget-add">
                    <button @click="setShowTasksDistributeDialog(true)">
                        {{ $gettext('Aufgabe verteilen') }}
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
    name: 'courseware-tasks-action-widget',
    components: {
        SidebarWidget,
    },
    methods: {
        ...mapActions({
            setShowTasksDistributeDialog: 'setShowTasksDistributeDialog',
        }),
    }
}
</script>