const getDefaultState = () => {
    return {
        typeFilter: 'all',
        unitFilter: 'all',
    };
};

const initialState = getDefaultState();

const getters = {
    typeFilter(state) {
        return state.typeFilter;
    },
    unitFilter(state) {
        return state.unitFilter;
    },
};

export const state = { ...initialState };

export const actions = {
    // setters
    setTypeFilter({ commit }, context) {
        commit('setTypeFilter', context);
    },

    setUnitFilter({ commit }, context) {
        commit('setUnitFilter', context);
    },

    // other actions
};

export const mutations = {
    setTypeFilter(state, data){
        state.typeFilter = data;
    },

    setUnitFilter(state, data){
        state.unitFilter = data;
    },
};

export default {
    state,
    actions,
    mutations,
    getters,
};
