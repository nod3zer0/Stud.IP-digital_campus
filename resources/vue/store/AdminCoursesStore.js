import Screenreader from '../../assets/javascripts/lib/screenreader.js';
import { $gettext } from '../../assets/javascripts/lib/gettext.js';

export default {
    namespaced: true,

    state: () => ({
        actionArea: 1,
        activatedFields: [],
        buttons: {
            top: '',
            bottom: '',
        },
        courses: [],
        coursesCount: null,
        coursesLoaded: false,
        filters: {},
        loadingXhr: false,
    }),

    getters: {
        getCourseById: (state) => (courseId) => {
            return state.courses.find((course) => course.id === courseId);
        },
        isLoading(state) {
            return state.loadingXhr !== null;
        }
    },

    mutations: {
        setActionArea(state, area) {
            state.actionArea = area;
        },
        setActivatedFields(state, fields) {
            state.activatedFields = fields;
        },
        setButtons(state, { top, bottom }) {
            state.buttons.top = top ?? state.buttons.top;
            state.buttons.bottom = bottom ?? state.buttons.bottom;
        },
        setCourse(state, payload) {
            state.courses = state.courses.filter(c => c.id !== payload.courseId);
            if (payload.data) {
                state.courses.push(payload.data);
            }
        },
        setCourses(state, courses, count = null) {
            state.courses = courses;
            state.coursesCount = count ?? courses.length;
        },
        setCoursesLoaded(state, loaded = true) {
            state.coursesLoaded = loaded;
        },
        setFilter(state, filters) {
            state.filters = {
                ...state.filters,
                ...filters,
            };
        },
    },

    actions: {
        loadCourse({ commit, state }, courseId) {
            $.getJSON(STUDIP.URLHelper.getURL('dispatch.php/admin/courses/search'), {
                ...state.filters,
                course_id: courseId,
                action: state.actionArea,
            }).done((response) =>  {
                commit('setCourse', {
                    courseId,
                    data: response.data[0] ?? null
                });
            });

        },
        loadCourses({ commit, state }, {withoutLimit = false, withoutScreenreaderNotice = false} = {}) {
            if (state.loadingXhr) {
                state.loadingXhr.abort();
            }

            Screenreader.notify('');

            let params = {
                ...state.filters,
                action: state.actionArea,
                activated_fields: state.activatedFields,
                without_limit: withoutLimit ? 1 : null,
            };

            // Remove empty items from params
            params = Object.keys(params)
                .filter((k) => params[k])
                .reduce((a, k) => ({ ...a, [k]: params[k] }), {});

            let timeout = null;
            if (!withoutScreenreaderNotice && !state.coursesLoaded) {
                timeout = setTimeout(() => {
                    STUDIP.Screenreader.notify($gettext('Suche läuft.'));
                }, 800);
            }

            const xhr = $.ajax({
                type: 'GET',
                url: STUDIP.URLHelper.getURL('dispatch.php/admin/courses/search'),
                dataType: 'json',
                data: params,
            });
            xhr.done((response) => {
                commit('setCoursesLoaded');

                if (response.data === undefined) {
                    commit('setCourses', [], response.count);
                } else {
                    commit('setCourses', response.data);
                }

                commit('setButtons', {
                    top: response.buttons_top ?? null,
                    bottom: response.buttons_bottom ?? null,
                });
            }).always(() => {
                clearTimeout(timeout);
                state.loadingXhr = null;
            });

            state.loadingXhr = xhr;
        },
        changeActionArea({ commit, state, dispatch }, area) {
            if (state.actionArea !== area) {
                commit('setActionArea', area);
                dispatch('loadCourses');
            }
        },
        changeFilter({ commit, state, dispatch }, filters) {
            const changed = Object.entries(filters).some(([key, value]) => {
                return state.filters[key] === undefined || state.filters[key] !== value;
            });
            if (changed) {
                commit('setFilter', filters);
                dispatch('loadCourses');
            }
        },
        toggleActiveField({ commit, state, dispatch }, field) {
            let fields = state.activatedFields;
            if (fields.includes(field)) {
                fields = fields.filter(f => f !== field);
            } else {
                fields.push(field);
            }

            commit('setActivatedFields', fields);
            dispatch('loadCourses');
        },
        toggleCompletionState({ commit, getters }, courseId) {
            $.get(
                STUDIP.URLHelper.getURL('dispatch.php/admin/courses/toggle_complete/' + courseId)
            ).done((response) => {
                const course = getters.getCourseById(courseId);
                commit('setCourse', {
                    courseId,
                    data: {
                        ...course,
                        completion: response.state,
                    }
                });
            });

        }
    }
}
