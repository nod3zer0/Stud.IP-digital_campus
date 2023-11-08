import PublicApp from './components/courseware/PublicApp.vue';
import CoursewarePublicModule from './store/courseware/courseware-public.module';
import PublicCoursewareStructuralElement from './components/courseware/structural-element/PublicCoursewareStructuralElement.vue';
import CoursewarePublicStructureModule from './store/courseware/public-structure.module';
import PluginManager from './components/courseware/plugin-manager.js';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import axios from 'axios';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import _ from 'lodash';

const mountApp = (STUDIP, createApp, element) => {

    let elem_id = null;
    let link_id = null;
    let link_pass = null;
    let entry_type = null;
    let elem = document.getElementById(element.substring(1));

    if (elem !== undefined) {
        if (elem.attributes !== undefined) {
            if (elem.attributes['entry-element-id'] !== undefined) {
                elem_id = elem.attributes['entry-element-id'].value;
            }

            if (elem.attributes['entry-type'] !== undefined) {
                entry_type = elem.attributes['entry-type'].value;
            }

            if (elem.attributes['link-id'] !== undefined) {
                link_id = elem.attributes['link-id'].value;
            }

            if (elem.attributes['link-pass'] !== undefined) {
                link_pass = elem.attributes['link-pass'].value;
            }
        }
    }

    const getHttpClient = () =>
    axios.create({
        baseURL: STUDIP.URLHelper.getURL('jsonapi.php/v1/public/courseware/'  + link_id, {}, true),
        headers: {
            'Content-Type': 'application/vnd.api+json',
        },
    });

    let base = new URL(
        STUDIP.URLHelper.getURL('dispatch.php/courseware/public', { link: link_id }, true)
    );

    const httpClient = getHttpClient();

    const store = new Vuex.Store({
        modules: {
            // courseware: CoursewareModule,
            'courseware-public': CoursewarePublicModule,
            'courseware-structure': CoursewarePublicStructureModule,
            ...mapResourceModules({
                names: [
                    'courseware-blocks',
                    'courseware-containers',
                    'courseware-instances',
                    'courseware-structural-elements',
                    'courseware-user-data-fields',
                    'courseware-user-progresses',
                    'files',
                    'file-refs',
                    'folders',
                    'users',
                ],
                httpClient,
            }),
        },
    });

    store.dispatch('setContext', {
        id: link_id,
        type: entry_type,
        rootId: elem_id
    });

    if (link_pass) {
        store.dispatch('setPassword', link_pass);
    } else {
        store.dispatch('setIsAuthenticated', true);
    }

    const pluginManager = new PluginManager();
    store.dispatch('setPluginManager', pluginManager);
    STUDIP.eventBus.emit('courseware:init-plugin-manager', pluginManager);

    const routes = [
        {
            path: '/',
            redirect: '/structural_element/' + elem_id,
        },
        {
            path: '/structural_element/:id',
            name: 'PublicCoursewareStructuralElement',
            component: PublicCoursewareStructuralElement,
            beforeEnter: (to, from, next) => {
                if (!store.getters.isAuthenticated) {
                    return false;
                }
                next();
            },
        },
    ];

    const router = new VueRouter({
        base: `${base.pathname}${base.search}`,
        routes,
    });

    const app = createApp({
        render: (h) => h(PublicApp),
        router,
        store
    });

    app.$mount(element);

    return app;
}

export default mountApp;
