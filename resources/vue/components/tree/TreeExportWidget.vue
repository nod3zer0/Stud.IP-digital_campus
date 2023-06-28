<template>
    <sidebar-widget v-if="exportData.length > 0" id="export-widget" class="sidebar-export" :title="$gettext('Export')">
        <template #content>
            <form class="sidebar-export">
                <studip-icon shape="export" :size="16"></studip-icon>
                <a :href="url" :title="title" @click.prevent="createExport()">{{ title }}</a>
            </form>
        </template>
    </sidebar-widget>
</template>

<script>
import axios from 'axios';
import SidebarWidget from '../SidebarWidget.vue';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'TreeExportWidget',
    components: {
        SidebarWidget, StudipIcon
    },
    props: {
        url: {
            type: String,
            required: true
        },
        title: {
            type: String,
            required: true
        },
        exportData: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        createExport() {
            const fd = new FormData();
            fd.append('courses', this.exportData.map(entry => entry.id));
            axios.post(
                this.url,
                fd,
                { headers: { 'Content-Type': 'multipart/form-data' }}
            ).then(response => {
                window.open(response.data);
            });
        }
    }
}
</script>
