const getDefaultState = () => {
    return {
        context: null,
        httpClient: null,
        userId: null,
        userIsTeacher: false,
        teacherStatusLoaded: false,
        typeFilter: 'all', // all, blocks, elements
        createdFilter: 'all', // all, oneDay, oneWeek
        unitFilter: 'all', // all or unit id
    };
};

const initialState = getDefaultState();

const getters = {
    context(state) {
        return state.context;
    },
    httpClient(state) {
        return state.httpClient;
    },
    userId(state) {
        return state.userId;
    },
    userIsTeacher(state) {
        return state.userIsTeacher;
    },
    teacherStatusLoaded(state) {
        return state.teacherStatusLoaded;
    },
    typeFilter(state) {
        return state.typeFilter;
    },
    createdFilter(state) {
        return state.createdFilter;
    },
    unitFilter(state) {
        return state.unitFilter;
    }
};

export const state = { ...initialState };

export const actions = {
    // setters
    setContext({ commit }, context) {
        commit('setContext', context);
    },
    setHttpClient({ commit }, httpClient) {
        commit('setHttpClient', httpClient);
    },
    setUserId({ commit }, id) {
        commit('setUserId', id);
    },
    setTypeFilter({ commit }, type) {
        commit('setTypeFilter', type);
    },
    setCreatedFilter({ commit }, created) {
        commit('setCreatedFilter', created);
    },
    setUnitFilter({ commit }, id) {
        commit('setUnitFilter', id);
    },
    // other actions
    async loadTeacherStatus({ dispatch, rootGetters, state, commit, getters }, userId) {
        const user = rootGetters['users/byId']({ id: userId });

        if (user.attributes.permission === 'root') {
            commit('setUserIsTeacher', true);
            return;
        }
        if (user.attributes.permission === 'admin') {
            await dispatch('courses/loadById', { id: state.context.id });
            const course = rootGetters['courses/byId']({id: state.context.id });
            const instituteId = course.relationships.institute.data.id;

            const parent = { type: 'users', id: `${userId}` };
            const relationship = 'institute-memberships';
            const options = {};
            await dispatch('institute-memberships/loadRelated', { parent, relationship, options }, { root: true });
            const instituteMemberships = rootGetters['institute-memberships/all'];
            const instituteMembership = instituteMemberships.filter(membership => membership.relationships.institute.data.id === instituteId);

            if (instituteMembership.length > 0 && instituteMembership[0].attributes.permission === 'admin') {
                commit('setUserIsTeacher', true);
                return;
            }
        }

        const membershipId = `${state.context.id}_${userId}`;
        try {
            await dispatch('course-memberships/loadById', { id: membershipId });
        } catch (error) {
            console.error(`Could not find course membership for ${membershipId}.`);
            commit('setUserIsTeacher', false);

            return false;
        }
        const membership = rootGetters['course-memberships/byId']({ id: membershipId });
        if (membership) {
            const membershipPermission = membership.attributes.permission;
            const isTeacher = membershipPermission === 'dozent' || membershipPermission === 'tutor';
            commit('setUserIsTeacher', isTeacher);

            return true;
        } else {
            console.error(`Could not find course membership for ${membershipId}.`);
            commit('setUserIsTeacher', false);

            return false;
        }
    },
};

export const mutations = {
    setContext(state, data) {
        state.context = data;
    },
    setHttpClient(state, data) {
        state.httpClient = data;
    },
    setUserId(state, data) {
        state.userId = data;
    },
    setTypeFilter(state, data) {
        state.typeFilter = data;
    },
    setCreatedFilter(state, data) {
        state.createdFilter = data;
    },
    setUnitFilter(state, data) {
        state.unitFilter = data;
    },
    setUserIsTeacher(state, isTeacher) {
        state.teacherStatusLoaded = true;
        state.userIsTeacher = isTeacher;
    },
};

export default {
    state,
    actions,
    mutations,
    getters,
};