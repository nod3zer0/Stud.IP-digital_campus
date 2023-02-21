import CoursewareShelfModule from './store/courseware/courseware-shelf.module';
import ShelfApp from './components/courseware/ShelfApp.vue';
import Vuex from 'vuex';
import axios from 'axios';
import { mapResourceModules } from '@elan-ev/reststate-vuex';

const mountApp = async (STUDIP, createApp, element) => {
    const getHttpClient = () =>
        axios.create({
            baseURL: STUDIP.URLHelper.getURL(`jsonapi.php/v1`, {}, true),
            headers: {
                'Content-Type': 'application/vnd.api+json',
            },
        });

    let elem;
    let entry_id = null;
    let entry_type = null;
    let licenses = null;

    if ((elem = document.getElementById(element.substring(1))) !== undefined) {
        if (elem.attributes !== undefined) {
            if (elem.attributes['entry-type'] !== undefined) {
                entry_type = elem.attributes['entry-type'].value;
            }

            if (elem.attributes['entry-id'] !== undefined) {
                entry_id = elem.attributes['entry-id'].value;
            }

            if (elem.attributes['licenses'] !== undefined) {
                licenses = JSON.parse(elem.attributes['licenses'].value);
            }
        }
    }

    const httpClient = getHttpClient();

    const store = new Vuex.Store({
        modules: {
            'courseware-shelf': CoursewareShelfModule,
            ...mapResourceModules({
                names: [
                    'courses',
                    'course-memberships',
                    'courseware-blocks',
                    'courseware-containers',
                    'courseware-instances',
                    'courseware-units',
                    'courseware-user-data-fields',
                    'courseware-user-progresses',
                    'courseware-structural-elements',
                    'courseware-structural-elements-shared',
                    'files',
                    'file-refs',
                    'folders',
                    'users',
                    'institutes',
                    'institute-memberships',
                    'semesters',
                    'sem-classes',
                    'sem-types',
                    'terms-of-use'
                ],
                httpClient,
            }),
        },
    });
    store.dispatch('setUrlHelper', STUDIP.URLHelper);
    store.dispatch('setHttpClient', httpClient);
    store.dispatch('setLicenses', licenses);
    store.dispatch('setUserId', STUDIP.USER_ID);
    await store.dispatch('users/loadById', {id: STUDIP.USER_ID});
    store.dispatch('setContext', {
        id: entry_id,
        type: entry_type,
    });
    if (entry_type === 'courses') {
        await store.dispatch('loadTeacherStatus', STUDIP.USER_ID);
        await store.dispatch('loadCourseUnits', entry_id);
    } else {
        await store.dispatch('loadUserUnits', entry_id);
        await store.dispatch('courseware-structural-elements-shared/loadAll', { options: { include: 'owner' } });
    }

    const app = createApp({
        render: (h) => h(ShelfApp),
        store,
    });

    app.$mount(element);

};

export default mountApp;
