const getDefaultState = () => {
    return {
        adminViewMode: 'templates',
        showAddTemplateDialog: false,
    };
};

const initialState = getDefaultState();

const getters = {
    adminViewMode(state) {
        return state.adminViewMode;
    },
    showAddTemplateDialog(state) {
        return state.showAddTemplateDialog;
    },
};

export const state = { ...initialState };

export const actions = {
    // setters
    adminViewMode(context, view) {
        context.commit('setAdminViewMode', view);
    },
    showAddTemplateDialog(context, showDialog) {
        context.commit('setShowAddTemplateDialog', showDialog);
    },

    // other actions
};

export const mutations = {
    setAdminViewMode(state, mode) {
        state.adminViewMode = mode;
    },
    setShowAddTemplateDialog(state, showDialog) {
        state.showAddTemplateDialog = showDialog;
    },
};

export default {
    state,
    actions,
    mutations,
    getters,
};
