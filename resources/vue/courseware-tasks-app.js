import TaskGroupsIndex from './components/courseware/tasks/PagesTaskGroupsIndex.vue';
import TaskGroupsShow from './components/courseware/tasks/PagesTaskGroupsShow.vue';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import VueRouter, { RouterView } from 'vue-router';
import Vuex from 'vuex';
import CoursewareModule from './store/courseware/courseware.module';
import CoursewareTasksModule from './store/courseware/courseware-tasks.module';
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

    const routes = [
        {
            path: '/',
            name: 'task-groups-index',
            component: TaskGroupsIndex,
        },
        {
            path: '/task-groups/:id',
            name: 'task-groups-show',
            component: TaskGroupsShow,
            props: true,
        },
    ];

    const base = new URL(
        window.STUDIP.URLHelper.getURL(
            'dispatch.php/course/courseware/tasks',
            { cid: STUDIP.URLHelper.parameters.cid },
            true
        )
    );
    const router = new VueRouter({
        base: base.pathname,
        mode: 'history',
        routes,
    });
    router.beforeEach((to, from, next) => {
        if ('cid' in to?.query) {
            next();
        } else {
            next({ ...to, query: { ...to.query, cid: window.STUDIP.URLHelper.parameters.cid } });
        }
    });

    const store = new Vuex.Store({
        modules: {
            courseware: CoursewareModule,
            tasks: CoursewareTasksModule,
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
    await store.dispatch('users/loadById', { id: STUDIP.USER_ID });
    store.dispatch('setHttpClient', httpClient);
    store.dispatch('coursewareContext', {
        id: entry_id,
        type: entry_type,
    });
    await store.dispatch('loadTeacherStatus', STUDIP.USER_ID);
    await store.dispatch('tasks/loadTasksOfCourse', { cid: entry_id });

    const app = createApp({
        render: (h) => h(RouterView),
        router,
        store,
    });

    app.$mount(element);

    return app;
};

export default mountApp;
