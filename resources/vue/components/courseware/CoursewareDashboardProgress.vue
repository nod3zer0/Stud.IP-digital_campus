<template>
    <div class="cw-dashboard-progress">
        <nav aria-label="Breadcrumb" class="cw-dashboard-progress-breadcrumb">
            <a
                v-if="parent"
                href="#"
                :title="$gettext('Hauptseite')"
                @click="visitRoot"
            >
                <studip-icon shape="home" />
            </a>
            <a
                v-if="parent"
                href="#"
                :title="parent.name"
                @click="selectChapter(parent.id)"
            >
                / {{ parent.name }}
            </a>
        </nav>
        <div v-if="selected" class="cw-dashboard-progress-chapter">
            <a :href="chapterUrl" :title="$gettextInterpolate('%{ pageTitle } öffnen', {pageTitle: selected.name})">
                <h1>{{ selected.name }}</h1>
            </a>
            <courseware-progress-circle
                :title="$gettext('diese Seite inkl. darunter liegende Seiten')"
                :value="parseInt(selected.progress.cumulative)"
            />
            <courseware-progress-circle
                :title="$gettext('diese Seite')"
                class="cw-dashboard-progress-current"
                :value="parseInt(selected.progress.self)"
            />
        </div>
        <div class="cw-dashboard-progress-subchapter-list">
            <courseware-dashboard-progress-item
                v-for="chapter in children"
                :key="chapter.id"
                :name="chapter.name"
                :value="chapter.progress.cumulative"
                :chapterId="chapter.id"
                @selectChapter="selectChapter"
            />
            <div v-if="!children.length" class="cw-dashboard-empty-info">
                <courseware-companion-box 
                    mood="sad"
                    :msgCompanion="$gettext('Diese Seite enthält keine darunter liegenden Seiten.')"
                />
            </div>
        </div>
    </div>
</template>

<script>
import StudipIcon from '../StudipIcon.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareDashboardProgressItem from './CoursewareDashboardProgressItem.vue';
import CoursewareProgressCircle from './CoursewareProgressCircle.vue';

export default {
    name: 'courseware-dashboard-progress',
    components: {
        CoursewareCompanionBox,
        CoursewareDashboardProgressItem,
        CoursewareProgressCircle,
        StudipIcon,
    },
    data() {
        return {
            selected: null,
        };
    },
    computed: {
        progressData() {
            return STUDIP.courseware_progress_data;
        },
        chapterUrl() {
            return (
                STUDIP.URLHelper.base_url +
                'dispatch.php/course/courseware/?cid=' +
                STUDIP.URLHelper.parameters.cid +
                '#/structural_element/' +
                this.selected.id
            );
        },
        parent() {
            if (!this.selected?.parent_id) {
                return null;
            }

            return this.progressData[this.selected.parent_id];
        },
        children() {
            if (!this.selected) {
                return [];
            }

            return Object.values(this.progressData).filter(({ parent_id }) => parent_id === this.selected.id);
        },
    },
    methods: {
        visitRoot() {
            this.selected = Object.values(this.progressData).find(({ parent_id }) => !!parent_id) ?? null;
        },
        selectChapter(id) {
            this.selected = this.progressData[id] ?? null;
        },
    },
    mounted() {
        this.visitRoot();
    },
};
</script>
