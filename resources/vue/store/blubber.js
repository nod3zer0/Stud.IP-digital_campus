function BlubberComment() {}

BlubberComment.prototype.isMine = function () {
    return this.author?.id === window.STUDIP.USER_ID;
};

function transformComment(rootGetters, { type, id, attributes, relationships }) {
    const author = relationships.author.data ? rootGetters['users/byId']({ id: relationships.author.data.id }) : null;

    return Object.assign(new BlubberComment(), {
        type,
        id,
        ...attributes,
        author: author ? transformUser(rootGetters, author) : null,
    });
}

function transformThread(rootGetters, { type, id, attributes, relationships, meta }) {
    const author = rootGetters['users/related']({
        parent: { id, type },
        relationship: 'author',
    });
    return {
        type,
        id,
        ...attributes,
        author: author ? transformUser(rootGetters, author) : null,
        avatar: meta.avatar,
        unseenComments: relationships.comments?.links.related.meta['unseen-comments'] ?? 0,
    };
}

function transformUser(rootGetters, { type, id, attributes, meta }) {
    return {
        type,
        id,
        ...attributes,
        avatar: meta.avatar,
    };
}

export default {
    namespaced: true,
    state: {
        hasMoreThreads: false,
        loadingNewer: {},
        loadingOlder: {},
        loadingThreads: false,
        moreNewer: {},
        moreOlder: {},
    },
    getters: {
        comments(state, getters, rootState, rootGetters) {
            return (threadId) => {
                const rawComments = rootGetters['blubber-comments/all'].filter(
                    (comment) => comment.relationships.thread.data.id === threadId
                );

                return rawComments.map((comment) => transformComment(rootGetters, comment));
            };
        },

        hasMoreThreads(state) {
            return state.hasMoreThreads;
        },

        isLoadingNewer(state) {
            return (threadId) => !!state.loadingNewer[threadId];
        },
        isLoadingOlder(state) {
            return (threadId) => !!state.loadingOlder[threadId];
        },

        isLoadingThreads(state) {
            return state.loadingThreads;
        },

        moreNewer(state) {
            return (threadId) => !!state.moreNewer[threadId];
        },
        moreOlder(state) {
            return (threadId) => !!state.moreOlder[threadId];
        },
        pollingParams(state, getters, rootState, rootGetters) {
            return () => ({
                threads: rootGetters['blubber-threads/all'].map(({ id }) => id),
            });
        },
        thread(state, getters, rootState, rootGetters) {
            return (threadId) => {
                const rawThread = rootGetters['blubber-threads/byId']({ id: threadId });

                return rawThread ? transformThread(rootGetters, rawThread) : null;
            };
        },
        threads(state, getters, rootState, rootGetters) {
            return rootGetters['blubber-threads/all'].map((thread) => transformThread(rootGetters, thread));
        },
    },
    mutations: {
        setHasMoreThreads(state, hasMoreThreads) {
            state.hasMoreThreads = hasMoreThreads;
        },
        setLoadingNewer(state, { id, loading }) {
            state.loadingNewer = { ...state.loadingNewer, [id]: loading };
        },
        setLoadingOlder(state, { id, loading }) {
            state.loadingOlder = { ...state.loadingOlder, [id]: loading };
        },
        setLoadingThreads(state, loadingThreads) {
            state.loadingThreads = loadingThreads;
        },
        setMoreNewer(state, { id, hasMore }) {
            state.moreNewer = { ...state.moreNewer, [id]: hasMore };
        },
        setMoreOlder(state, { id, hasMore }) {
            state.moreOlder = { ...state.moreOlder, [id]: hasMore };
        },
    },
    actions: {
        changeThreadSubscription({ dispatch, rootGetters }, { id, subscribe }) {
            return STUDIP.Blubber.followunfollow(id, subscribe).done((state) => {
                const thread = rootGetters['blubber-threads/byId']({ id });
                thread.attributes['is-followed'] = state;

                return dispatch('blubber-threads/storeRecord', thread, { root: true });
            });
        },

        createComment({ dispatch, rootGetters }, { id, content }) {
            const data = {
                attributes: { content },
                relationships: {
                    thread: {
                        data: {
                            type: 'blubber-threads',
                            id,
                        },
                    },
                },
            };
            return dispatch('blubber-comments/create', data, { root: true });
        },

        destroyComment({ dispatch }, { id }) {
            return dispatch('blubber-comments/delete', { id }, { root: true });
        },

        async fetchThread({ commit, dispatch, rootGetters }, { id, search }) {
            const options = {
                include: 'author',
                sort: '-mkdate',
            };
            if (search) {
                options['filter[search]'] = search;
            }

            await Promise.all([
                dispatch('blubber-threads/loadById', { id }, { root: true }),
                dispatch(
                    'blubber-comments/loadRelated',
                    { parent: { type: 'blubber-threads', id }, relationship: 'comments', options },
                    { root: true }
                ),
            ]);

            // loadCurrentUser is nice enough to know whether it still needs to load the current user
            await dispatch('loadCurrentUser');

            // if total is missing, there are more comments to fetch
            const total = rootGetters['blubber-comments/lastMeta']?.page?.total;
            const hasMore = !total;
            commit('setMoreOlder', { id, hasMore });
        },

        async fetchThreads({ commit, dispatch, getters, rootGetters }, { search, more = false }) {
            if (getters.isLoadingThreads) {
                return;
            }

            commit('setLoadingThreads', true);

            const options = {
                'page[limit]': 20,
            };
            const filter = {};
            if (search) {
                filter['search'] = search;
            }
            if (more) {
                const earliestDate = rootGetters['blubber-threads/all'].reduce((earliest, thread) => {
                    const activityDate = new Date(thread.attributes['latest-activity']);
                    return !earliest || activityDate < earliest ? activityDate : earliest;
                }, null);
                if (earliestDate) {
                    filter['before'] = earliestDate.toISOString();
                }
            }

            await dispatch('blubber-threads/loadWhere', { filter, options }, { root: true });

            const total = rootGetters['blubber-threads/lastMeta']?.page?.total;
            const hasMore = !total;
            commit('setHasMoreThreads', hasMore);

            commit('setLoadingThreads', false);
        },

        loadCurrentUser({ dispatch, rootGetters }) {
            const myUserId = window.STUDIP.USER_ID;
            if (!rootGetters['users/byId']({ id: myUserId })) {
                return dispatch('users/loadById', { id: myUserId }, { root: true });
            }
        },

        async loadNewerComments({ commit, dispatch, getters, rootGetters }, { id, search }) {
            if (!getters.moreNewer(id)) {
                return;
            }

            const latestMkdate = getters.comments(id).reduce((latest, comment) => {
                const mkdate = new Date(comment.mkdate);
                return (latest ?? 0) < mkdate ? mkdate : latest;
            }, null);

            if (!getters.isLoadingNewer(id)) {
                commit('setLoadingNewer', { id, loading: true });

                const options = {
                    include: 'author,thread',
                    sort: 'mkdate',
                };
                if (latestMkdate) {
                    options['filter[since]'] = latestMkdate.toISOString();
                }

                if (search) {
                    options['filter[search]'] = search;
                }

                await dispatch(
                    'blubber-comments/loadRelated',
                    {
                        parent: { type: 'blubber-threads', id },
                        relationship: 'comments',
                        options,
                    },
                    { root: true }
                );

                // if total is missing, there are more comments to fetch
                commit('setMoreNewer', {
                    id,
                    hasMore: !('total' in rootGetters['blubber-comments/lastMeta'].page),
                });

                commit('setLoadingNewer', { id, loading: false });
            }
        },

        async loadOlderComments({ commit, dispatch, getters, rootGetters }, { id, search }) {
            if (!getters.moreOlder(id)) {
                return;
            }
            const earliestMkdate = getters.comments(id).reduce((earliest, comment) => {
                const mkdate = new Date(comment.mkdate);
                return !earliest || earliest > mkdate ? mkdate : earliest;
            }, null);

            if (!getters.isLoadingOlder(id)) {
                commit('setLoadingOlder', { id, loading: true });

                const options = {
                    include: 'author,thread',
                    sort: '-mkdate',
                };
                if (earliestMkdate) {
                    options['filter[before]'] = earliestMkdate.toISOString();
                }
                if (search) {
                    options['filter[search]'] = search;
                }

                await dispatch(
                    'blubber-comments/loadRelated',
                    {
                        parent: { type: 'blubber-threads', id },
                        relationship: 'comments',
                        options,
                    },
                    { root: true }
                );

                // if total is missing, there are more comments to fetch
                commit('setMoreOlder', {
                    id,
                    hasMore: !('total' in rootGetters['blubber-comments/lastMeta'].page),
                });

                commit('setLoadingOlder', { id, loading: false });
            }
        },

        markThreadAsSeen({ dispatch, rootGetters }, { id }) {
            const thread = rootGetters['blubber-threads/byId']({ id });
            const meta = thread.relationships.comments?.links.related.meta;
            if (meta?.['unseen-comments']) {
                thread.attributes['visited-at'] = new Date().toISOString();
                thread.relationships.comments.links.related.meta = { ...meta, 'unseen-comments': 0 };
                dispatch('blubber-threads/update', thread, { root: true });
            }
        },

        setThreadAsDefault({ dispatch, rootGetters }, { id }) {
            const parent = rootGetters['users/byId']({ id: window.STUDIP.USER_ID });

            return dispatch(
                'blubber-threads/setRelated',
                {
                    parent,
                    relationship: 'blubber-default-thread',
                    data: { type: "blubber-threads", id },
                },
                { root: true }
            );
        },

        updateComment({ dispatch }, { id, content }) {
            const data = {
                type: 'blubber-comments',
                id,
                attributes: { content },
            };
            return dispatch('blubber-comments/update', data, { root: true }).then(() =>
                dispatch('blubber-comments/loadById', { id }, { root: true })
            );
        },

        updateState({ commit, dispatch }, datagram) {
            Object.entries(datagram).forEach(([method, data]) => {
                if (method === 'addNewComments') {
                    return Promise.all(
                        Object.keys(data).map((id) => {
                            commit('setMoreNewer', { id, hasMore: true });
                            return dispatch('loadNewerComments', { id });
                        })
                    );
                } else if (method === 'removeDeletedComments') {
                    return Promise.all(
                        data.map((id) => {
                            return dispatch('blubber-comments/removeRecord', { id }, { root: true });
                        })
                    );
                } else if (method === 'updateThreadWidget') {
                    return Promise.all(
                        data.map(({ thread_id }) =>
                            dispatch('blubber-threads/loadById', { id: thread_id }, { root: true })
                        )
                    );
                }
            });
        },
    },
};
