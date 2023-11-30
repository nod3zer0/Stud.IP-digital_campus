<template>
    <sidebar-widget id="courseware-action-widget" :title="$gettext('Aktionen')" v-if="structuralElement">
        <template #content>
            <ul class="widget-list widget-links cw-action-widget">
                <li v-if="canEdit" class="cw-action-widget-add">
                    <button @click="addElement">
                        {{ $gettext('Seite hinzufügen') }}
                    </button>
                </li>
                <li class="cw-action-widget-export">
                    <button @click="exportElement">
                        {{ $gettext('Seite exportieren') }}
                    </button>
                </li>
            </ul>
        </template>
    </sidebar-widget>
</template>

<script>
import SidebarWidget from '../../SidebarWidget.vue';
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
            showElementExportChooserDialog: 'showElementExportChooserDialog',
        }),
        addElement() {
            this.showElementAddDialog(true);
        },
        exportElement() {
            this.showElementExportChooserDialog(true);
        }
    },
};
</script>
