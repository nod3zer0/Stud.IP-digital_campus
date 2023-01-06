import ActivitiesApp from './components/courseware/ActivitiesApp.vue';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import Vuex from 'vuex';
import CoursewareModule from './store/courseware/courseware.module';
import CoursewareActivitiesModule from './store/courseware/courseware-activities.module';
import CoursewareStructureModule from './store/courseware/structure.module';
import axios from 'axios';

const mountApp = async (STUDIP, createApp, element) => {
    const getHttpClient = () =>
        axios.create({
            baseURL: STUDIP.URLHelper.getURL(`jsonapi.php/v1`, {}, true),
            headers: {
                'Content-Type': 'application/vnd.api+json',
            },
        });

    const httpClient = getHttpClient();

    const store = new Vuex.Store({
        modules: {
            courseware: CoursewareModule,
            'courseware-structure': CoursewareStructureModule,
            'courseware-activities': CoursewareActivitiesModule,
            ...mapResourceModules({
                names: [
                    'activities',
                    'users',
                    'courses',
                    'course-memberships',
                    'courseware-blocks',
                    'courseware-block-comments',
                    'courseware-block-feedback',
                    'courseware-containers',
                    'courseware-instances',
                    'courseware-structural-elements',
                    'courseware-task-feedback',
                    'courseware-task-groups',
                    'courseware-tasks',
                    'courseware-units',
                    'courseware-user-data-fields',
                    'courseware-user-progresses',
                    'files',
                    'file-refs',
                    'folders',
                    'users',
                    'institutes',
                    'semesters',
                    'sem-classes',
                    'sem-types',
                    'status-groups',
                ],
                httpClient,
            }),
        },
    });
    let entry_id = null;
    let entry_type = null;
    let elem;

    if ((elem = document.getElementById(element.substring(1))) !== undefined) {
        if (elem.attributes !== undefined) {
            if (elem.attributes['entry-type'] !== undefined) {
                entry_type = elem.attributes['entry-type'].value;
            }

            if (elem.attributes['entry-id'] !== undefined) {
                entry_id = elem.attributes['entry-id'].value;
            }
        }
    }

    store.dispatch('setUserId', STUDIP.USER_ID);
    await store.dispatch('users/loadById', {id: STUDIP.USER_ID});
    store.dispatch('setHttpClient', httpClient);
    store.dispatch('coursewareContext', {
        id: entry_id,
        type: entry_type,
    });
    await store.dispatch('loadTeacherStatus', STUDIP.USER_ID);
    await store.dispatch('loadCourseUnits', entry_id);

    const app = createApp({
        render: (h) => h(ActivitiesApp),
        store,
    });

    app.$mount(element);

    return app;
};

export default mountApp;
