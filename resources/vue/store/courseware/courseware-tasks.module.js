const getDefaultState = () => {
    return {
        showTasksDistributeDialog: false,
    };
};

const initialState = getDefaultState();

const getters = {
    showTasksDistributeDialog(state) {
        return state.showTasksDistributeDialog;
    },
};

export const state = { ...initialState };

export const actions = {
    // setters
    setShowTasksDistributeDialog({ commit }, context) {
        commit('setShowTasksDistributeDialog', context);
    },

    // other actions
};

export const mutations = {
    setShowTasksDistributeDialog(state, data){
        state.showTasksDistributeDialog = data;
    },
};

export default {
    state,
    actions,
    mutations,
    getters,
};
