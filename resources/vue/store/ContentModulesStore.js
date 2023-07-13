export default {
    namespaced: true,

    state: () => ({
        categories: [],
        filterCategory: null,
        highlighted: [],
        modules: [],
        userId: null,
        view: 'tiles',
    }),
    getters: {
        getModuleById: (state) => (moduleId) => {
            return state.modules.find(module => module.id === moduleId);
        },
    },
    mutations: {
        setCategories(state, categories) {
            state.categories = categories;
        },
        setFilterCategory(state, category) {
            state.filterCategory = category;
        },
        setHighlighted(state, highlighted) {
            state.highlighted = highlighted;
        },
        setModule(state, module) {
            let modules = state.modules.filter(m => m.id !== module.id);
            modules.push(module);

            state.modules = modules;
        },
        setModules(state, modules) {
            state.modules = modules;
        },
        setUserId(state, userId) {
            state.userId = userId;
        },
        setView(state, view) {
            state.view = view;
        },
    },
    actions: {
        changeView({ commit, state }, view) {
            commit('setView', view);

            const documentId = `${state.userId}_CONTENTMODULES_TILED_DISPLAY`;

            const data = {
                id: documentId,
                type: 'config-values',
                attributes: { value: view === 'tiles' }
            };

            return STUDIP.jsonapi.PATCH(`config-values/${documentId}`, { data: { data } }) ;
        },
        exchangeModules({ commit, state }, modules) {
            const order = modules.filter(module => module.active)
                .sort((a, b) => a.position - b.position)
                .map(module => module.id);
            return $.post(
                STUDIP.URLHelper.getURL('dispatch.php/course/contentmodules/reorder'),
                { order }
            ).then((output) => {
                commit('setModules', modules);

                return output;
            });
        },
        setModuleActive({ commit, state, getters }, { moduleId, active }) {
            const module = getters.getModuleById(moduleId);
            module.active = active;

            return $.post(
                STUDIP.URLHelper.getURL('dispatch.php/course/contentmodules/trigger'),
                {
                    moduleclass: module.moduleclass,
                    plugin_id: module.id,
                    active: module.active ? 1 : 0
                }
            ).done((output) => {
                module.position = output.position;
                commit('setModule', module);

                return output;
            });
        },
        setModuleVisible({ commit, state, getters }, { moduleId, visible }) {
            const module = getters.getModuleById(moduleId);

            return $.post(
                STUDIP.URLHelper.getURL('dispatch.php/course/contentmodules/change_visibility'),
                {
                    moduleclass: module.moduleclass,
                    plugin_id: module.id,
                    visible: visible ? 1 : 0,
                }
            ).done((output) => {
                module.visibility = output.visibility;
                commit('setModule', module);
            });
        },
        swapModules({ dispatch, state, getters }, { moduleA, moduleB }) {
            let modules = state.modules.map(module => {
                if (module.id === moduleA.id) {
                    return {
                        ...moduleA,
                        position: moduleB.position,
                    };
                }

                if (module.id === moduleB.id) {
                    return {
                        ...moduleB,
                        position: moduleA.position,
                    };
                }

                return module;
            });
            return dispatch('exchangeModules', modules);
        },
    }
}
