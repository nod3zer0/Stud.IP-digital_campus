const getDefaultState = () => {
    return {
        blockAdder: {},
        consumeMode: true,
        containerAdder: false,
        context: null,
        courseware: {},
        isAuthenticated: false,
        password: null,
        pluginManager: null,
        selectedToolbarItem: 'contents',
        showToolbar: false,
        userId: null,
        viewMode: 'read',
    };
};

const initialState = getDefaultState();

const getters = {
    blockAdder(state) {
        return state.blockAdder;
    },

    consumeMode(state) {
        return state.consumeMode;
    },

    containerAdder(state) {
        return state.containerAdder;
    },

    context(state) {
        return state.context;
    },

    courseware(state) {
        return state.courseware;
    },

    isAuthenticated(state) {
        return state.isAuthenticated;
    },

    password(state) {
        return state.password;
    },

    pluginManager(state) {
        return state.pluginManager;
    },

    selectedToolbarItem(state) {
        return state.selectedToolbarItem;
    },

    showToolbar(state) {
        return state.showToolbar;
    },

    userId(state) {
        return state.userId;
    },

    viewMode(state) {
        return state.viewMode;
    },
};

export const state = { ...initialState };

export const actions = {
    // setters
    coursewareConsumeMode({ commit }, consumeMode) {
        commit('setConsumeMode', consumeMode);
    },

    coursewareContainerAdder(context, adder) {
        context.commit('setContainerAdder', adder);
    },

    coursewareShowToolbar(context, toolbar) {
        context.commit('setShowToolbar', toolbar);
    },

    coursewareViewMode(context, view) {
        context.commit('setViewMode', view);
    },

    setContext({ commit }, context) {
        commit('setContext', context);
    },

    setPluginManager({ commit }, pluginManager) {
        commit('setPluginManager', pluginManager);
    },

    setIsAuthenticated({ commit }, isAuthenticated) {
        commit('setIsAuthenticated', isAuthenticated);
    },

    setPassword({ commit }, password) {
        commit('setPassword', password);
    },

    // other actions
    loadStructuralElement({ dispatch }, structuralElementId) {
        const options = {
            include:
                'containers,containers.blocks',
        };

        return dispatch(
            'courseware-structural-elements/loadById',
            { id: structuralElementId, options },
            { root: true }
        );
    },

    validatePassword({ getters, dispatch }, password) {
        if (password === getters.password) {
            dispatch('setIsAuthenticated', true);

            return true;
        }

        return false;
    }
};

export const mutations = {

    coursewareSet(state, data) {
        state.courseware = data;
    },

    setConsumeMode(state, consumeMode) {
        state.consumeMode = consumeMode;
    },

    setContainerAdder(state, containerAdder) {
        state.containerAdder = containerAdder;
    },

    setContext(state, context) {
        state.context = context;
    },

    setIsAuthenticated(state, isAuthenticated) {
        state.isAuthenticated = isAuthenticated;
    },

    setPassword(state, password) {
        state.password = password;
    },

    setPluginManager(state, pluginManager) {
        state.pluginManager = pluginManager;
    },

    setShowToolbar(state, showToolbar) {
        state.showToolbar = showToolbar;
    },

    setViewMode(state, data) {
        state.viewMode = data;
    },
};

export default {
    state,
    actions,
    mutations,
    getters,
};
