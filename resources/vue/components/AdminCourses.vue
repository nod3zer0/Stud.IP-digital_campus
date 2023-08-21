<template>
    <table class="default">
        <caption>
            {{ $gettext('Veranstaltungen') }}
            <span class="actions" v-if="isLoading">
                <img :src="loadingIndicator" width="20" height="20" :title="$gettext('Daten werden geladen')">
            </span>
            <span class="actions" v-else-if="coursesCount > 0">
                {{ coursesCount + ' ' + $gettext('Veranstaltungen') }}
            </span>
        </caption>
        <thead>
            <tr class="sortable">
                <th v-if="showComplete">
                    <a
                        @click.prevent="changeSort('completion')"
                        class="course-completion"
                        :title="$gettext('Bearbeitungsstatus')"
                    >
                        {{ $gettext('Bearbeitungsstatus') }}
                    </a>
                    <studip-icon :shape="sort.direction === 'ASC' ? 'arr_1down' : 'arr_1up'"
                                 v-if="sort.by === 'completion'"
                                 class="text-bottom"></studip-icon>
                </th>
                <th v-for="activeField in sortedActivatedFields" :key="`field-${activeField}`">
                    <a href="#"
                       @click.prevent="changeSort(activeField)"
                       :title="sort.by === activeField && sort.direction === 'ASC' ? $gettextInterpolate('Sortiert aufsteigend nach %{field}', {field: fields[activeField]}) : (sort.by === activeField && sort.direction === 'DESC' ? $gettextInterpolate('Sortiert absteigend nach %{ field } ', { field: fields[activeField]}) : $gettextInterpolate('Sortieren nach %{ field }', { field: fields[activeField]}))"
                       v-if="!unsortableFields.includes(activeField)"
                    >
                        {{ fields[activeField] }}
                        <studip-icon :shape="sort.direction === 'ASC' ? 'arr_1down' : 'arr_1up'"
                                     v-if="sort.by === activeField"
                                     class="text-bottom"></studip-icon>
                    </a>
                    <template v-else>
                        {{ fields[activeField] }}
                    </template>
                </th>
                <th class="actions" style="text-align: center;">
                    {{ $gettext('Aktion') }}
                </th>
            </tr>
            <tr v-if="buttons.top">
                <th v-html="buttons.top" style="text-align: right" :colspan="colspan"></th>
            </tr>
        </thead>
        <tbody :class="{ loading: isLoading }">
            <tr v-for="course in sortedCourses"
                :key="course.id"
                :class="course.id === currentLine ? 'selected' : ''"
                @click="currentLine = course.id">
                <td v-if="showComplete">
                    <button :href="getURL('dispatch.php/admin/courses/toggle_complete/' + course.id)"
                            class="course-completion undecorated"
                            :data-course-completion="course.completion"
                            :title="(course.completion > 0 ? (course.completion == 1 ? $gettext('Veranstaltung in Bearbeitung.') : $gettext('Veranstaltung komplett.')) : $gettext('Veranstaltung neu.')) + ' ' +  $gettext('Klicken zum Ändern des Status.')"
                            @click.prevent="toggleCompletionState(course.id)">
                        {{ $gettext('Bearbeitungsstatus ändern') }}
                    </button>
                </td>
                <td v-for="active_field in sortedActivatedFields" :key="active_field">
                    <div v-html="course[active_field]"></div>
                    <a v-if="active_field === 'name' && getChildren(course).length > 0"
                       @click.prevent="toggleOpenChildren(course.id)"
                       href="">
                        <studip-icon :shape="open_children.indexOf(course.id) === -1 ? 'add' : 'remove'" class="text-bottom"></studip-icon>
                        {{ $gettextInterpolate(
                            $gettext('%{ n } Unterveranstaltungen'),
                            { n: getChildren(course).length }
                        ) }}
                    </a>
                </td>
                <td class="actions" v-html="course.action">
                </td>
            </tr>
            <tr v-if="coursesCount === 0 && coursesLoaded">
                <td :colspan="colspan">
                    {{ $gettext('Keine Ergebnisse') }}
                </td>
            </tr>
            <tr v-if="coursesCount > 0 && sortedCourses.length === 0">
                <td :colspan="colspan">
                    {{
                        $gettextInterpolate(
                            $gettext(`%{ n } Veranstaltungen entsprechen Ihrem Filter. Schränken Sie nach Möglichkeit die Filter weiter ein.`),
                            { n: coursesCount }
                        )
                    }}
                    <a href="" @click.prevent="loadCourses({withoutLimit: true});">
                        {{ $gettext('Alle anzeigen') }}
                    </a>
                </td>
            </tr>
            <tr v-if="!coursesLoaded">
                <td :colspan="colspan">
                    {{ $gettext('Daten werden geladen ...') }}
                </td>
            </tr>
        </tbody>
        <tfoot v-if="buttons.bottom">
            <tr>
                <td v-html="buttons.bottom" style="text-align: right" :colspan="colspan"></td>
            </tr>
        </tfoot>
    </table>

</template>
<script>
import { mapActions, mapGetters, mapState } from 'vuex';

export default {
    name: 'AdminCourses',
    props: {
        maxCourses: Number,
        showComplete: {
            type: Boolean,
            default: false,
        },
        fields: Object,
        unsortableFields: Array,
        sortBy: String,
        sortFlag: String,
    },
    data() {
        return {
            sort: {
                by: this.sortBy,
                direction: this.sortFlag,
            },
            currentLine: null,
            open_children: [],
        };
    },
    created() {
        this.loadCourses();
    },
    computed: {
        ...mapState('admincourses', [
            'activatedFields',
            'buttons',
            'courses',
            'coursesCount',
            'coursesLoaded',
            'filters',
        ]),
        ...mapGetters('admincourses', [
            'isLoading',
        ]),
        colspan () {
            let colspan = this.activatedFields.length + 1;
            if (this.showComplete) {
                colspan += 1;
            }
            return colspan;
        },
        sortedCourses() {
            let maincourses = this.courses.filter(c => !c.parent_course);
            maincourses = this.sortArray(maincourses);

            let sorted_courses = [];
            let children = [];
            for (let i in maincourses) {
                sorted_courses.push(maincourses[i]);
                if (this.open_children.indexOf(maincourses[i].id) !== -1) {
                    children = this.getChildren(maincourses[i]);
                    children = this.sortArray(children);
                    for (let k in children) {
                        sorted_courses.push(children[k]);
                    }
                }
            }
            return sorted_courses;
        },
        sortedActivatedFields() {
            return Object.keys(this.fields).filter(f => this.activatedFields.includes(f));
        },
        loadingIndicator() {
            return STUDIP.ASSETS_URL + 'images/loading-indicator.svg';
        }
    },
    methods: {
        ...mapActions('admincourses', [
            'changeActionArea',
            'changeFilter',
            'loadCourses',
            'loadCourse',
            'toggleActiveField',
            'toggleCompletionState',
        ]),
        getChildren(course) {
            return this.courses.filter(c => c.parent_course === course.id);
        },
        toggleOpenChildren(course_id) {
            if (!this.open_children.includes(course_id)) {
                this.open_children.push(course_id);
            } else {
                this.open_children = this.open_children.filter(cid => cid !== course_id);
            }
        },
        changeSort(column) {
            if (this.sort.by === column) {
                this.sort.direction = this.sort.direction === 'ASC' ? 'DESC' : 'ASC';
            } else {
                this.currentLine = null;
                this.sort.direction = 'ASC';
            }
            this.sort.by = column;

            $.post(STUDIP.URLHelper.getURL('dispatch.php/admin/courses/sort'), {
                sortby: column,
                sortflag: this.sort.direction,
            });
        },
        sortArray (array) {
            if (!array.length) {
                return [];
            }
            let sortby = this.sort.by;
            if (!this.activatedFields.includes(sortby) && sortby !== 'completion') {
                return array;
            }

            const striptags = function (text) {
                if (typeof text === "string") {
                    return text.replace(/(<([^>]+)>)/gi, "");
                } else {
                    return text;
                }
            };

            // Define sort direction by this factor
            const directionFactor = this.sort.direction === 'ASC' ? 1 : -1;

            // Default sort function by string comparison of field
            const collator = new Intl.Collator(String.locale, {numeric: true, sensitivity: 'base'});
            let sortFunction = function (a, b) {
                return collator.compare(striptags(a[sortby]), striptags(b[sortby]));
            };

            let is_numeric = true;
            for (let i in array) {
                if (striptags(array[i][sortby]) && isNaN(striptags(array[i][sortby]))) {
                    is_numeric = false;
                    break;
                }
            }
            if (is_numeric) {
                sortFunction = function (a, b) {
                    return (striptags(a[sortby]) ? parseInt(striptags(a[sortby]), 10) : 0)
                        - (striptags(b[sortby]) ? parseInt(striptags(b[sortby]), 10) : 0);
                };
            }

            // Actual sort on copy of array
            return array.concat().sort((a, b) => directionFactor * sortFunction(a, b));
        },
        getURL(url, params = {}) {
            return STUDIP.URLHelper.getURL(url, params);
        },
    }
};
</script>
