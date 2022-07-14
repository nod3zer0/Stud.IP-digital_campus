import AdminApp from './components/courseware/AdminApp.vue';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import Vuex from 'vuex';
import CoursewareAdminModule from './store/courseware/courseware-admin.module';
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
            courseware: CoursewareAdminModule,
            ...mapResourceModules({
                names: [
                    'courseware-templates',
                ],
                httpClient,
            }),
        },
    });

    store.dispatch('courseware-templates/loadAll');

    const app = createApp({
        render: (h) => h(AdminApp),
        store
    });

    app.$mount(element);

    return app;
}

export default mountApp;