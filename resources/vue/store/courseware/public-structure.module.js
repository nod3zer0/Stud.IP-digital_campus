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
    build({commit, rootGetters }) {
        const context = rootGetters['context'];
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

        const ordered = [...visitTree(children, context.rootId)];
        commit('setOrdered', ordered);
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
