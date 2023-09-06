import ContentBookmarkApp from './components/courseware/ContentBookmarkApp.vue';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import Vuex from 'vuex';
import CoursewareModule from './store/courseware/courseware.module';
import axios from 'axios';

const mountApp = (STUDIP, createApp, element) => {
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
            ...mapResourceModules({
                names: [
                    'activities',
                    'file-refs',
                    'courses',
                    'course-memberships',
                    'courseware-blocks',
                    'courseware-block-comments',
                    'courseware-block-feedback',
                    'courseware-containers',
                    'courseware-instances',
                    'courseware-structural-elements',
                    'courseware-units',
                    'courseware-user-data-fields',
                    'courseware-user-progresses',
                    'institutes',
                    'semesters',
                    'sem-classes',
                    'sem-types',
                    'status-groups',
                    'users',
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
    store.dispatch('users/loadById', {id: STUDIP.USER_ID});
    store.dispatch('loadUsersBookmarks', STUDIP.USER_ID);
    store.dispatch('setHttpClient', httpClient);
    store.dispatch('coursewareContext', {
        id: entry_id,
        type: entry_type,
    });

    const app = createApp({
        render: (h) => h(ContentBookmarkApp),
        store
    });

    app.$mount(element);

    return app;
}

export default mountApp;
