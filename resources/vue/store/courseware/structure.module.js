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
        const structuralElements = rootGetters['courseware-structural-elements/all'];
        const root = findRoot(structuralElements);
        if (!root) {
            commit('reset');

            return;
        }

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

    async load({ commit, dispatch, rootGetters }) {
        const parent = rootGetters['context'];
        const relationship = 'courseware';
        const options = {
            include: 'bookmarks,root',
        };

        // get courseware instance
        await dispatch(`courseware-instances/loadRelated`, { parent, relationship, options }, { root: true });
        const courseware = rootGetters['courseware-instances/all'][0];
        commit('coursewareSet', courseware, { root: true });

        // load descendants
        dispatch('fetchDescendants');
    },

    async fetchDescendants({ dispatch, rootGetters, commit }) {
        // get root of that instance
        const courseware = rootGetters['courseware'];
        if (!courseware) {
            return;
        }
        const rootElement = rootGetters['courseware-structural-elements/related']({
            parent: { id: courseware.id, type: courseware.type },
            relationship: 'root',
        });
        if (!rootElement) {
            return;
        }

        const cache = window.STUDIP.Cache.getInstance('courseware');
        const cacheKey = `descendants/${rootElement.id}/${rootGetters['userId']}`;

        await unpickleDescendants();
        revalidateDescendants();

        function unpickleDescendants() {
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
            return loadDescendants().then(removeStaleElements).then(pickleDescendants);
        }

        function loadDescendants() {
            const parent = { id: rootElement.id, type: rootElement.type };
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
        }

        function pickleDescendants() {
            try {
                cache.set(cacheKey, rootGetters['courseware-structural-elements/all']);
            } catch (e) {}
        }

        function removeStaleElements() {
            const idsToKeep = [
                rootElement.id,
                ...rootGetters['courseware-structural-elements/related']({
                    parent: rootElement,
                    relationship: 'descendants',
                }).map(({ id }) => id),
            ];
            rootGetters['courseware-structural-elements/all']
                .map(({ id }) => id)
                .filter((id) => !idsToKeep.includes(id))
                .forEach((id) => commit('courseware-structural-elements/REMOVE_RECORD', { id }, { root: true }));
        }
    },
};

function findRoot(nodes) {
    return nodes.find((node) => !node.relationships.parent?.data);
}

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
