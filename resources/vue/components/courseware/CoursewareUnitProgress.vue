<template>
    <div class="cw-unit-progress">
        <p v-if="userIsTeacher">
            {{
                $gettext('Die Fortschrittsanzeige bezieht sich auf den Anteil der Teilnehmenden, die eine Seite aufgerufen haben.')
            }}
        </p>
        <nav aria-label="Breadcrumb" class="cw-unit-progress-breadcrumb">
            <a v-if="parent" href="#" :title="$gettext('Hauptseite')" @click="visitRoot">
                <studip-icon shape="home" />
            </a>
            <a v-if="parent && parent.id !== rootId" href="#" :title="parent.name" @click="selectChapter(parent.id)">
                | {{ parent.name }}
            </a>
        </nav>
        <div v-if="selected" class="cw-unit-progress-chapter">
            <h1>
                <a
                    :href="chapterUrl"
                    :title="$gettextInterpolate('%{ pageTitle } öffnen', { pageTitle: selected.name })"
                >
                    {{ selected.name }}
                </a>
            </h1>
            <courseware-progress-circle
                :title="selected.id === rootId ? $gettext('Gesamtes Lernmaterial') : $gettext('diese Seite inkl. darunter liegender Seiten')"
                :value="parseInt(selected.progress.cumulative)"
            />
            <courseware-progress-circle
                :title="$gettext('diese Seite')"
                class="cw-unit-progress-current"
                :value="parseInt(selected.progress.self)"
            />
        </div>
        <div v-if="children.length > 0" class="cw-unit-progress-subchapter-list">
            <courseware-unit-progress-item
                v-for="chapter in children"
                :key="chapter.id"
                :name="chapter.name"
                :value="chapter.progress.cumulative"
                :chapterId="chapter.id"
                @selectChapter="selectChapter"
            />
        </div>
        <div v-else class="cw-unit-empty-info">
            <courseware-companion-box
                mood="sad"
                :msgCompanion="$gettext('Diese Seite enthält keine darunter liegenden Seiten.')"
            />
        </div>
    </div>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareUnitProgressItem from './CoursewareUnitProgressItem.vue';
import CoursewareProgressCircle from './CoursewareProgressCircle.vue';
import StudipIcon from '../StudipIcon.vue';

import { mapGetters } from 'vuex';

export default {
    name: 'courseware-unit-progress',
    components: {
        CoursewareCompanionBox,
        CoursewareUnitProgressItem,
        CoursewareProgressCircle,
        StudipIcon,
    },
    props: {
        progressData: Object,
        unitId: String,
        rootId: Number,
    },
    data() {
        return {
            selected: null,
        };
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher',
        }),
        chapterUrl() {
            return (
                STUDIP.URLHelper.getURL('dispatch.php/course/courseware/courseware/' + this.unitId) +
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
            this.selected = this.progressData[this.rootId];
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
