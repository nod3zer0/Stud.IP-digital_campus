import ContentReleasesApp from './components/courseware/ContentReleasesApp.vue';
import CoursewareModule from './store/courseware/courseware.module';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import Vuex from 'vuex';
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
                    'courseware-containers',
                    'courseware-public-links',
                    'courseware-structural-elements',
                    'file-refs',
                    'users',
                ],
                httpClient,
            }),
        },
    });
    let entry_id = null;
    let entry_type = null;
    let elem = document.getElementById(element.substring(1));

    if (elem !== undefined) {
        if (elem.attributes !== undefined) {
            if (elem.attributes['entry-type'] !== undefined) {
                entry_type = elem.attributes['entry-type'].value;
            }

            if (elem.attributes['entry-id'] !== undefined) {
                entry_id = elem.attributes['entry-id'].value;
            }
        }
    }

    store.dispatch('coursewareContext', {
        id: entry_id,
        type: entry_type,
    });

    store.dispatch('courseware-public-links/loadAll', {
        options: {
            include: 'structural-element',
        },
    });

    const app = createApp({
        render: (h) => h(ContentReleasesApp),
        store
    });

    app.$mount(element);

    return app;
}

export default mountApp;