<template>
    <ul class="widget-list widget-links cw-action-widget">
        <li class="cw-action-widget-add" @click="addElement">
            <translate>Neues Lernmaterial anlegen</translate>
        </li>
    </ul>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'courseware-content-overview-action-widget',
    methods: {
        ...mapActions({
            setShowOverviewElementAddDialog: 'setShowOverviewElementAddDialog'
        }),
        addElement() {
            this.setShowOverviewElementAddDialog(true);
        }
    }
}
</script>
