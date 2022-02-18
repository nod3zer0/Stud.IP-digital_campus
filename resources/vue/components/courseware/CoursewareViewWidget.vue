<template>
    <ul class="widget-list widget-links sidebar-views cw-view-widget">
        <li :class="{ active: readView }">
            <a href="#" @click="setReadView">
                <translate>Lesen</translate>
            </a>
        </li>
        <li :class="{ active: editView }">
            <a href="#" @click="setEditView">
                <translate>Bearbeiten</translate>
            </a>
        </li>
        <li 
            v-if="context.type === 'courses' && canVisit"
            :class="{ active: discussView }"
        >
            <a href="#" @click="setDiscussView">
                <translate>Diskutieren</translate>
            </a>
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
        }
    },
    methods: {
        ...mapActions({
            coursewareViewMode: 'coursewareViewMode',
            coursewareBlockAdder: 'coursewareBlockAdder',
            setToolbarItem: 'coursewareSelectedToolbarItem',
        }),
        setReadView() {
            this.coursewareViewMode('read');
            this.setToolbarItem('contents');
            this.coursewareBlockAdder({});
        },
        setEditView() {
            this.coursewareViewMode('edit');
        },
        setDiscussView() {
            this.$store.dispatch('coursewareViewMode', 'discuss');
        },
    },
};
</script>
