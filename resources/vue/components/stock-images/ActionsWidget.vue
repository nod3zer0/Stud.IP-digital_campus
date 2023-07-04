<template>
    <SidebarWidget :title="$gettext('Aktionen')">
        <template #content>
            <ul class="widget-list widget-links">
                <li>
                    <studip-icon shape="upload" class="widget-action-icon" />
                    <button @click="onUploadClick">{{ $gettext('Bild hinzufügen') }}</button>
                </li>
            </ul>
        </template>
    </SidebarWidget>
</template>
<script>
import SidebarWidget from '../SidebarWidget.vue';

export default {
    components: {
        SidebarWidget,
    },
    methods: {
        onUploadClick() {
            this.$emit('initiateUpload');
        },
    },
};
</script>
<style scoped>
.widget-list li {
    position: relative;
}
.widget-action-icon {
    position: absolute;
    left: 0;
}
</style>
