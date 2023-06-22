import CoursewareCommentsModule from './store/courseware/courseware-comments.module';
import CommentsApp from './components/courseware/CommentsApp.vue';
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

    let elem = document.getElementById(element.substring(1));
    let entry_id = null;
    let entry_type = null;

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

    const httpClient = getHttpClient();

    const store = new Vuex.Store({
        modules: {
            'courseware-comments': CoursewareCommentsModule,
            ...mapResourceModules({
                names: [
                    'courseware-blocks',
                    'courseware-block-comments',
                    'courseware-block-feedback',
                    'courseware-containers',
                    'courseware-units',
                    'courseware-structural-elements',
                    'courseware-structural-element-comments',
                    'courseware-structural-element-feedback',
                    'users',
                    'course-memberships',
                    'institutes',
                    'institute-memberships',
                ],
                httpClient,
            }),
        },
    });
    store.dispatch('setHttpClient', httpClient);
    store.dispatch('setContext', {
        id: entry_id,
        type: entry_type,
    });
    store.dispatch('setUserId', STUDIP.USER_ID);
    await store.dispatch('users/loadById', { id: STUDIP.USER_ID });
    await store.dispatch('loadTeacherStatus', STUDIP.USER_ID);

    const data = await axios(STUDIP.URLHelper.getURL('dispatch.php/course/courseware/comments_overview_data/'));
    store.commit(
        'courseware-units/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['units']).data,
        { root: true }
    );
    store.commit(
        'courseware-structural-elements/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['elements']).data,
        { root: true }
    );
    store.commit(
        'courseware-containers/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['containers']).data,
        { root: true }
    );
    store.commit(
        'courseware-blocks/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['blocks']).data,
        { root: true }
    );
    store.commit(
        'courseware-block-comments/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['block_comments']).data,
        { root: true }
    );
    store.commit(
        'courseware-block-feedback/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['block_feedbacks']).data,
        { root: true }
    );
    store.commit(
        'courseware-structural-element-comments/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['element_comments']).data,
        { root: true }
    );
    store.commit(
        'courseware-structural-element-feedback/REPLACE_ALL_RECORDS',
        JSON.parse(data.data['element_feedbacks']).data,
        { root: true }
    );

    const app = createApp({
        render: (h) => h(CommentsApp),
        store,
    });

    app.$mount(element);
};

export default mountApp;
