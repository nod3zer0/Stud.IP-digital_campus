const state = () => ({
    httpClient: null,
});

const getters = {
    allTags(state, getters, rootState, rootGetters) {
        return Array.from(
            rootGetters['stock-images/all'].reduce((tags, stockImage) => {
                stockImage.attributes.tags.forEach((tag) => tags.add(tag));
                return tags;
            }, new Set())
        );
    },
};

const mutations = {
    setHttpClient(state, httpClient) {
        state.httpClient = httpClient;
    },
};

const actions = {
    async create({ dispatch, rootGetters, state }, [file, metadata]) {
        const stockImage = {
            type: 'stock-images',
            attributes: {
                title: metadata.title,
                description: metadata.description,
                author: metadata.author,
                license: metadata.license,
                tags: metadata.tags,
            },
        };
        await dispatch('stock-images/create', stockImage, { root: true });

        const created = rootGetters['stock-images/lastCreated'];
        const formData = new FormData();
        formData.append('image', file);

        await state.httpClient.post(`stock-images/${created.id}/blob`, formData);

        return dispatch('stock-images/loadById', created, { root: true });
    },

    async update({ dispatch, rootGetters, state }, { stockImage, attributes }) {
        console.debug('stockImage', stockImage);
        stockImage.attributes = { ...stockImage.attributes, ...attributes };
        await dispatch('stock-images/update', stockImage, { root: true });
        return dispatch('stock-images/loadById', stockImage, { root: true });
    },

    delete({ dispatch, rootGetters }, id) {
        const record = rootGetters['stock-images/byId']({ id });
        return dispatch('stock-images/delete', record, { root: true });
    },
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
};
