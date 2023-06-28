<template>
    <div v-if="isLoading">
        <studip-progress-indicator></studip-progress-indicator>
    </div>
    <article v-else class="studip-tree-table">
        <table v-if="courses.length > 0" class="default studip-tree-table">
            <caption>
                <studip-icon shape="search" :size="20"></studip-icon>
                {{ $gettextInterpolate($ngettext('Ein Eintrag für den Begriff "%{searchterm}" gefunden',
                    '%{count} Einträge für den Begriff "%{searchterm}" gefunden', courses.length),
                    { count: courses.length, searchterm: searchConfig.searchterm}) }}
            </caption>
            <colgroup>
                <col style="width: 30px">
                <col>
                <col>
            </colgroup>
            <thead>
            <tr>
                <th></th>
                <th>{{ $gettext('Name') }}</th>
                <th>{{ $gettext('Information') }}</th>
            </tr>
            </thead>
            <tbody>
                <tr v-for="(course) in courses" :key="course.id" class="studip-tree-child studip-tree-course">
                    <td>
                        <studip-icon shape="seminar" :size="26"></studip-icon>
                    </td>
                    <td>
                        <a :href="courseUrl(course.id)"
                           :title="$gettextInterpolate($gettext('Zur Veranstaltung %{name}'), {name:  + course.attributes.title})">
                            <template v-if="course.attributes['course-number']">
                                {{ course.attributes['course-number'] }}
                            </template>
                            {{ course.attributes.title }}
                            <div :id="'course-dates-' + course.id" class="course-dates"></div>
                        </a>
                        <tree-node-course-path :node-class="searchConfig.classname"
                                               :course-id="course.id"></tree-node-course-path>
                    </td>
                    <td>
                        <tree-course-details :course="course.id"></tree-course-details>
                    </td>
                </tr>
            </tbody>
        </table>
    </article>
</template>

<script>
import { TreeMixin } from '../../mixins/TreeMixin';
import StudipProgressIndicator from '../StudipProgressIndicator.vue';
import StudipIcon from '../StudipIcon.vue';
import TreeNodeCoursePath from './TreeNodeCoursePath.vue';
import TreeCourseDetails from './TreeCourseDetails.vue';

export default {
    name: 'TreeSearchResult',
    components: { StudipIcon, StudipProgressIndicator, TreeNodeCoursePath, TreeCourseDetails },
    mixins: [ TreeMixin ],
    props: {
        searchConfig: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            node: null,
            isLoading: false,
            isLoaded: false,
            courses: []
        }
    },
    mounted() {
        this.getNode(this.searchConfig.classname + '_root').then(response => {
            this.getNodeCourses(response.data.data, this.searchConfig.semester,0, this.searchConfig.searchterm, true)
                .then(courses => {
                    this.courses = courses.data.data;
                });
        });
    }
}
</script>
