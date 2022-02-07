<template>
    <div v-if="structureLoadingState === 'done'">
        <courseware-structural-element
            :canVisit="canVisit"
            :structural-element="selected"
            :ordered-structural-elements="orderedStructuralElements"
            @select="selectStructuralElement"
        ></courseware-structural-element>
        <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions">
            <courseware-action-widget :structural-element="selected" :canVisit="canVisit"></courseware-action-widget>
        </MountingPortal>
        <MountingPortal mountTo="#courseware-view-widget" name="sidebar-views">
            <courseware-view-widget :structural-element="selected" :canVisit="canVisit"></courseware-view-widget>
        </MountingPortal>
    </div>
    <studip-progress-indicator
        v-else
        class="cw-loading-indicator-content"
        :description="$gettext('Lade Lernmaterial...')"
    />
</template>

<script>
import CoursewareStructuralElement from './CoursewareStructuralElement.vue';
import CoursewareViewWidget from './CoursewareViewWidget.vue';
import CoursewareActionWidget from './CoursewareActionWidget.vue';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareStructuralElement,
        CoursewareViewWidget,
        CoursewareActionWidget,
        StudipProgressIndicator,
    },
    data: () => ({
        canVisit: null,
        selected: null,
        structureLoadingState: 'idle',
    }),
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            orderedStructuralElements: 'courseware-structure/ordered',
            relatedStructuralElement: 'courseware-structural-elements/related',
            structuralElementLastMeta: 'courseware-structural-elements/lastMeta',
            structuralElements: 'courseware-structural-elements/all',
            structuralElementById: 'courseware-structural-elements/byId',
            userId: 'userId',
        }),
    },
    methods: {
        ...mapActions({
            buildStructure: 'courseware-structure/build',
            coursewareBlockAdder: 'coursewareBlockAdder',
            invalidateStructureCache: 'courseware-structure/invalidateCache',
            loadCoursewareStructure: 'courseware-structure/load',
            loadStructuralElement: 'loadStructuralElement',
            loadTeacherStatus: 'loadTeacherStatus',
        }),
        async selectStructuralElement(id) {
            if (!id) {
                return;
            }

            await this.loadStructuralElement(id);
            this.canVisit = this.structuralElementLastMeta['can-visit'];
            this.selected = this.structuralElementById({ id });
        },
    },
    async mounted() {
        this.structureLoadingState = 'loading';
        await this.loadCoursewareStructure();
        await this.loadTeacherStatus(this.userId);
        this.structureLoadingState = 'done';
        const selectedId = this.$route.params?.id;
        await this.selectStructuralElement(selectedId);
    },
    watch: {
        $route(to) {
            // reset block adder on navigate
            this.coursewareBlockAdder({});

            const selectedId = to.params?.id;
            this.selectStructuralElement(selectedId);
        },
        async structuralElements(newElements, oldElements) {
            // compute order of structural elements once more
            await this.buildStructure();

            // throw away stale cache
            this.invalidateStructureCache();
        },
    },
};
</script>
