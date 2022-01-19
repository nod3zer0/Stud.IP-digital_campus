<template>
    <div class="cw-dashboard-progress">
        <div class="cw-dashboard-progress-breadcrumb">
            <span v-if="parent" @click="visitRoot"><studip-icon shape="home" /></span>
            <span v-if="parent" @click="selectChapter(parent.id)"> / {{ parent.name }}</span>
        </div>
        <div class="cw-dashboard-progress-chapter" v-if="selected">
            <h1>
                <a :href="chapterUrl">{{ selected.name }}</a>
            </h1>
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
            <div v-if="!children.length">
                <translate>Dieses Seite enthält keine darunter liegenden Seiten</translate>
            </div>
        </div>
    </div>
</template>

<script>
import StudipIcon from '../StudipIcon.vue';
import CoursewareDashboardProgressItem from './CoursewareDashboardProgressItem.vue';
import CoursewareProgressCircle from './CoursewareProgressCircle.vue';

export default {
    name: 'courseware-dashboard-progress',
    components: {
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
