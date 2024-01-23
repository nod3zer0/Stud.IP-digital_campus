const getDefaultState = () => {
    return {
        showTaskGroupsAddSolversDialog: false,
        showTaskGroupsDeleteDialog: false,
        showTaskGroupsModifyDeadlineDialog: false,
        showTasksDistributeDialog: false,
    };
};

const initialState = getDefaultState();

const getters = {
    showTaskGroupsAddSolversDialog(state) {
        return state.showTaskGroupsAddSolversDialog;
    },
    showTaskGroupsDeleteDialog(state) {
        return state.showTaskGroupsDeleteDialog;
    },
    showTaskGroupsModifyDeadlineDialog(state) {
        return state.showTaskGroupsModifyDeadlineDialog;
    },
    showTasksDistributeDialog(state) {
        return state.showTasksDistributeDialog;
    },
    taskGroupsByCid(state, getters, rootState, rootGetters) {
        return (cid) => {
            return rootGetters['courseware-task-groups/all'].filter(
                (taskGroup) => taskGroup.relationships.course.data.id === cid
            );
        };
    },
    tasksByCid(state, getters, rootState, rootGetters) {
        return (cid) => {
            const taskGroupIds = getters.taskGroupsByCid(cid).map(({ id }) => id);

            return rootGetters['courseware-tasks/all'].filter((task) =>
                taskGroupIds.includes(task.relationships['task-group'].data.id)
            );
        };
    },
};

export const state = { ...initialState };

export const actions = {
    // setters
    setShowTaskGroupsAddSolversDialog({ commit }, context) {
        commit('setShowTaskGroupsAddSolversDialog', context);
    },
    setShowTaskGroupsDeleteDialog({ commit }, context) {
        commit('setShowTaskGroupsDeleteDialog', context);
    },
    setShowTaskGroupsModifyDeadlineDialog({ commit }, context) {
        commit('setShowTaskGroupsModifyDeadlineDialog', context);
    },
    setShowTasksDistributeDialog({ commit }, context) {
        commit('setShowTasksDistributeDialog', context);
    },

    // other actions
    loadTasksOfCourse({ dispatch }, { cid }) {
        const options = {
            'filter[cid]': cid,
            include: 'solver, structural-element, task-feedback, task-group, task-group.lecturer',
        };
        return dispatch('courseware-tasks/loadAll', { options }, { root: true });
    },

    loadTaskGroup({ dispatch }, { id }) {
        const options = {
            include: 'lecturer',
        };
        return dispatch('courseware-task-groups/loadById', { id, options }, { root: true });
    },

    modifyDeadlineOfTaskGroup({ dispatch }, { taskGroup, endDate }) {
        taskGroup.attributes['end-date'] = endDate.toISOString();

        return dispatch('courseware-task-groups/update', taskGroup, { root: true });
    },

    addSolversToTaskGroup({ dispatch, rootGetters }, { taskGroup, solvers }) {
        return rootGetters.httpClient.post(`courseware-task-groups/${+taskGroup.id}/relationships/solvers`, {
            data: solvers,
        });
    },
};

export const mutations = {
    setShowTaskGroupsAddSolversDialog(state, data) {
        state.showTaskGroupsAddSolversDialog = data;
    },
    setShowTasksDistributeDialog(state, data) {
        state.showTasksDistributeDialog = data;
    },
    setShowTaskGroupsDeleteDialog(state, data) {
        state.showTaskGroupsDeleteDialog = data;
    },
    setShowTaskGroupsModifyDeadlineDialog(state, data) {
        state.showTaskGroupsModifyDeadlineDialog = data;
    },
};

export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters,
};
