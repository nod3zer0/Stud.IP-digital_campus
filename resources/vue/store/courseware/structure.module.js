const getDefaultState = () => {
    return {
        children: [],
        ordered: [],
    };
};

const initialState = getDefaultState();
const state = { ...initialState };

const getters = {
    children(state) {
        return (id) => state.children[id] ?? [];
    },
    ordered(state) {
        return state.ordered;
    },
};

export const mutations = {
    reset(state) {
        state = getDefaultState();
    },
    setChildren(state, children) {
        state.children = children;
    },
    setOrdered(state, ordered) {
        state.ordered = ordered;
    },
};

const actions = {
    build({ commit, rootGetters }) {
        const instance = rootGetters['courseware'];
        if (!instance) {
            throw new Error('Could not find current courseware');
        }
        const root = rootGetters['courseware-structural-elements/related']({
            parent: { id: instance.id, type: instance.type },
            relationship: 'root',
        });
        if (!root) {
            commit('reset');

            return;
        }

        const structuralElements = rootGetters['courseware-structural-elements/all'];
        const children = structuralElements.reduce((memo, element) => {
            const parent = element.relationships.parent?.data?.id ?? null;
            if (parent) {
                if (!memo[parent]) {
                    memo[parent] = [];
                }
                memo[parent].push([element.id, element.attributes.position]);
            }

            return memo;
        }, {});
        for (const key of Object.keys(children)) {
            children[key].sort((childA, childB) => childA[1] - childB[1]);
            children[key] = children[key].map(([id]) => id);
        }
        commit('setChildren', children);

        const ordered = [...visitTree(children, root.id)];
        commit('setOrdered', ordered);
    },

    invalidateCache({ rootGetters }) {
        const courseware = rootGetters['courseware'];
        if (!courseware) {
            return;
        }
        const element = rootGetters['courseware-structural-elements/related']({
            parent: { id: courseware.id, type: courseware.type },
            relationship: 'root',
        });
        if (!element) {
            return;
        }
        const cache = window.STUDIP.Cache.getInstance('courseware');
        const cacheKey = `descendants/${element.id}/${rootGetters['userId']}`;
        try {
            cache.remove(cacheKey);
        } catch (e) {
            // nothing we can do
        }
    },

    // load the structure of the current courseware
    async load({ commit, dispatch, rootGetters }) {
        const context = rootGetters['context'];
        const instance = await dispatch('loadInstance', context);
        commit('coursewareSet', instance, { root: true });

        const root = rootGetters['courseware-structural-elements/related']({
            parent: { id: instance.id, type: instance.type },
            relationship: 'root',
        });
        if (!root) {
            throw new Error(`Could not find root of courseware { id: ${instance.id}, type: ${instance.type}`);
        }

        dispatch('fetchDescendantsWithCaching', { root });

        return instance;
    },

    loadInstance({ commit, dispatch, rootGetters }, context) {
        let parent = context;
        parent = {
            type: context.type,
            id: context.id + '_' + context.unit
        }
        const relationship = 'courseware';
        const options = {
            include: 'bookmarks,root',
        };

        return dispatch(
            `courseware-instances/loadRelated`,
            {
                parent,
                relationship,
                options,
            },
            { root: true }
        ).then(() => {
            return rootGetters['courseware-instances/related']({ parent, relationship });
        });
    },

    async fetchDescendantsWithCaching({ dispatch, rootGetters, commit }, { root }) {
        const cache = window.STUDIP.Cache.getInstance('courseware');
        const cacheKey = `descendants/${root.id}/${rootGetters['userId']}`;

        await unpickleStaleDescendants();
        return revalidateDescendants();

        function unpickleStaleDescendants() {
            try {
                const descendants = cache.get(cacheKey);
                const cacheHit = descendants !== undefined;
                if (cacheHit) {
                    commit('courseware-structural-elements/REPLACE_ALL_RECORDS', descendants, { root: true });
                }
            } catch (e) {
                return;
            }
        }

        function revalidateDescendants() {
            return dispatch('loadDescendants', { root }).then(removeStaleElements).then(pickleDescendants);
        }

        function pickleDescendants() {
            try {
                cache.set(cacheKey, rootGetters['courseware-structural-elements/all']);
            } catch (e) {
                // No action necessary
            }
        }

        function removeStaleElements() {
            const idsToKeep = [
                root.id,
                ...rootGetters['courseware-structural-elements/related']({
                    parent: root,
                    relationship: 'descendants',
                }).map(({ id }) => id),
            ];
            rootGetters['courseware-structural-elements/all']
                .map(({ id }) => id)
                .filter((id) => !idsToKeep.includes(id))
                .forEach((id) => commit('courseware-structural-elements/REMOVE_RECORD', { id }, { root: true }));
        }
    },

    loadDescendants({ dispatch }, { root }) {
        const parent = { id: root.id, type: root.type };
        const relationship = 'descendants';
        const options = {
            'page[offset]': 0,
            'page[limit]': 10000,
        };

        return dispatch(
            'courseware-structural-elements/loadRelated',
            { parent, relationship, options },
            { root: true }
        );
    },
};

function* visitTree(tree, current) {
    if (current) {
        yield current;

        const children = tree[current];
        if (children) {
            for (let index = 0; index < children.length; index++) {
                yield* visitTree(tree, children[index]);
            }
        }
    }
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state,
};
