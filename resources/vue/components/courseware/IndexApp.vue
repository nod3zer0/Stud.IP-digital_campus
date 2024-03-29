<template>
    <div>
        <div v-if="structureLoadingState === 'done'">
            <courseware-search-results v-show="showSearchResults" />
            <courseware-structural-element
                v-show="!showSearchResults"
                :canVisit="canVisit"
                :structural-element="selected"
                :ordered-structural-elements="orderedStructuralElements"
                @select="selectStructuralElement"
            ></courseware-structural-element>
            <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions">
                <courseware-action-widget v-if="!showSearchResults && canEditSelected" :structural-element="selected"></courseware-action-widget>
            </MountingPortal>
            <MountingPortal mountTo="#courseware-search-widget" name="sidebar-search">
                <courseware-search-widget v-if="selected !== null"></courseware-search-widget>
            </MountingPortal>
        </div>
        <studip-progress-indicator
            v-if="structureLoadingState === 'loading'"
            class="loading-indicator-content"
            :description="$gettext('Lade Lernmaterial...')"
        />
        <courseware-companion-box
            v-if="structureLoadingState === 'error'"
            mood="sad"
            :msgCompanion="loadingErrorMessage"
        />
        <courseware-companion-overlay />
    </div>
</template>

<script>
import CoursewareStructuralElement from './structural-element/CoursewareStructuralElement.vue';
import CoursewareSearchResults from './structural-element/CoursewareSearchResults.vue';
import CoursewareCompanionBox from './layouts/CoursewareCompanionBox.vue';
import CoursewareCompanionOverlay from './layouts/CoursewareCompanionOverlay.vue';
import CoursewareActionWidget from './widgets/CoursewareActionWidget.vue';
import CoursewareSearchWidget from './widgets/CoursewareSearchWidget.vue';

import StudipProgressIndicator from '../StudipProgressIndicator.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareStructuralElement,
        CoursewareSearchResults,
        CoursewareActionWidget,
        CoursewareCompanionBox,
        StudipProgressIndicator,
        CoursewareSearchWidget,
        CoursewareCompanionOverlay,
    },
    data: () => ({
        canVisit: null,
        selected: null,
        structureLoadingState: 'idle',
        loadingErrorStatus: null
    }),
    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware',
            orderedStructuralElements: 'courseware-structure/ordered',
            relatedStructuralElement: 'courseware-structural-elements/related',
            showSearchResults: 'showSearchResults',
            structuralElementLastMeta: 'courseware-structural-elements/lastMeta',
            structuralElements: 'courseware-structural-elements/all',
            structuralElementById: 'courseware-structural-elements/byId',
            userId: 'userId',
            userIsTeacher: 'userIsTeacher'
        }),
        loadingErrorMessage() {
            switch (this.loadingErrorStatus) {
                case 404:
                    return this.$gettext('Die Seite konnte nicht gefunden werden.');
                case 403:
                    return this.$gettext('Diese Seite steht Ihnen leider nicht zur Verfügung.');
                default:
                    return this.$gettext('Beim Laden der Seite ist ein Fehler aufgetreten.');
            }
        },
        canEditSelected() {
            if (this.selected) {
                return this.selected.attributes['can-edit'];
            }

            return false;
        }
    },
    methods: {
        ...mapActions({
            buildStructure: 'courseware-structure/build',
            coursewareBlockAdder: 'coursewareBlockAdder',
            invalidateStructureCache: 'courseware-structure/invalidateCache',
            loadCoursewareStructure: 'courseware-structure/load',
            loadStructuralElement: 'loadStructuralElement',
        }),
        async selectStructuralElement(id) {
            if (!id) {
                return;
            }

            try {
                await this.loadStructuralElement(id);
            } catch (error) {
                this.loadingErrorStatus = error.status;
                this.structureLoadingState = 'error';
                return;
            }

            this.$nextTick( () => {
                this.canVisit = this.structuralElementLastMeta['can-visit'];
                this.selected = this.structuralElementById({ id });
            });
        },
    },
    async mounted() {
        this.structureLoadingState = 'loading';
        try {
            await this.loadCoursewareStructure();
        }
        catch (error) {
            this.loadingErrorStatus = error.status;
            this.structureLoadingState = 'error';
            return;
        }

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
            window.scrollTo({ top: 0 });
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
