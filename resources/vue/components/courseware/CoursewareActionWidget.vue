<template>
    <sidebar-widget id="courseware-action-widget" :title="$gettext('Aktionen')" v-if="structuralElement">
        <template #content>
            <ul class="widget-list widget-links cw-action-widget">
                <li v-if="canEdit" class="cw-action-widget-add">
                    <button @click="addElement">
                        {{ $gettext('Seite hinzufügen') }}
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
    name: 'courseware-action-widget',
    props: ['structuralElement'],
    components: {
        SidebarWidget,
    },
    computed: {
        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
        },
    },
    methods: {
        ...mapActions({
            showElementAddDialog: 'showElementAddDialog',
        }),
        addElement() {
            this.showElementAddDialog(true);
        },
    },
};
</script>
