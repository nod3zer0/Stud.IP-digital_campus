<template>
    <ul class="widget-list widget-links sidebar-views cw-view-widget">
        <li
            :class="{ active: readView }"
            @click="setReadView"
        >
            <translate>Lesen</translate>
        </li>
        <li
            v-if="canEdit"
            :class="{ active: editView }"
            @click="setEditView"
        >
            <translate>Bearbeiten</translate>
        </li>
        <li 
            v-if="context.type === 'courses' && canVisit"
            :class="{ active: discussView }"
            @click="setDiscussView"
        >
            <translate>Diskutieren</translate>
        </li>
    </ul>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-view-widget',
    props: ['structuralElement', 'canVisit'],
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
        ...mapActions(
            ['coursewareBlockAdder']
        ),
        setReadView() {
            this.$store.dispatch('coursewareViewMode', 'read');
            this.coursewareBlockAdder({});
        },
        setEditView() {
            this.$store.dispatch('coursewareViewMode', 'edit');
        },
        setDiscussView() {
            this.$store.dispatch('coursewareViewMode', 'discuss');
        },
    },
};
</script>
