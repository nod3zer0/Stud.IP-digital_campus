import ContentOverviewApp from './components/courseware/ContentOverviewApp.vue';
import CoursewareStructureModule from './store/courseware/structure.module';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import Vue from 'vue';
import Vuex from 'vuex';
import CoursewareModule from './store/courseware/courseware.module';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css'

Vue.component('v-select', vSelect);

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
            'courseware-structure': CoursewareStructureModule,
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
                    'courseware-structural-elements-shared',
                    'courseware-templates',
                    'courseware-user-data-fields',
                    'courseware-user-progresses',
                    'file-refs',
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
    let licenses = null;
    let elem;

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

    store.dispatch('setUserId', STUDIP.USER_ID);
    store.dispatch('users/loadById', {id: STUDIP.USER_ID});
    store.dispatch('courseware-structural-elements/loadById',{ id: STUDIP.COURSEWARE_USERS_ROOT_ID, options: { include: 'children'}});
    store.dispatch('courseware-templates/loadAll');
    store.dispatch('setHttpClient', httpClient);
    store.dispatch('licenses', licenses);
    store.dispatch('coursewareContext', {
        id: entry_id,
        type: entry_type,
    });

    store.dispatch('courseware-structural-elements-shared/loadAll', { options: { include: 'owner' } });

    const app = createApp({
        render: (h) => h(ContentOverviewApp),
        store
    });

    app.$mount(element);

    return app;
}

export default mountApp;
