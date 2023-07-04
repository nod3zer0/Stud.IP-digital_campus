const Blubber = {
    init() {
        const blubberPage = document.querySelector('#blubber-index, #messenger-course, .blubber_panel.vueinstance');
        if (blubberPage !== null) {
            const blubberPanel = document.querySelector('.blubber_panel');
            if (blubberPanel !== null) {
                connectBlubber(blubberPanel, 'BlubberCommunityPage');
            }
        }

        $(document).on('dialog-open', function (event, { dialog }) {
            const blubberPanel = dialog.querySelector('.blubber_panel');
            if (blubberPanel !== null) {
                connectBlubber(blubberPanel, 'BlubberDialogPanel');
            }
        });

        function connectBlubber(blubberPanel, componentName) {
            return Promise.all([window.STUDIP.Vue.load(), Blubber.plugin()]).then(
                ([{ Vue, createApp, store }, BlubberPlugin]) => {
                    Vue.use(BlubberPlugin, { store });
                    const { initialThreadId, search } = blubberPanel.dataset;
                    return createApp({
                        el: blubberPanel,
                        render: (h) => h(Vue.component(componentName), { props: { initialThreadId, search } }),
                    });
                }
            );
        }
    },
    plugin() {
        return import('@/vue/plugins/blubber.js').then(({ BlubberPlugin }) => BlubberPlugin);
    },
    refreshThread(data) {
        STUDIP.eventBus.emit('studip:select-blubber-thread', data.thread_id);
    },
    followunfollow(thread_id, follow) {
        const elements = $(`.blubber_panel .followunfollow[data-thread_id="${thread_id}"]`);
        if (follow === undefined) {
            follow = elements.hasClass('unfollowed');
        }
        elements.addClass('loading');

        return STUDIP.Vue.load().then(async ({ store }) => {
            return store.dispatch('studip/blubber/changeThreadSubscription', {
                id: thread_id,
                subscribe: follow,
            });
        }).then(() => {
            elements.toggleClass('unfollowed', !follow);
        }).finally(() => {
            elements.removeClass('loading');
        });
    },
    Composer: {
        vue: null,
        init() {
            STUDIP.Vue.load()
                .then(({ createApp }) => {
                    let components = STUDIP.Blubber.components;
                    return createApp({
                        el: '#blubber_contact_ids',
                        data() {
                            return {
                                users: [],
                            };
                        },
                        methods: {
                            addUser: function (user_id, name) {
                                this.users.push({
                                    user_id: user_id,
                                    name: name,
                                });
                            },
                            removeUser: function (event) {
                                let user_id = $(event.target).closest('li').find('input').val();
                                for (let i in this.users) {
                                    if (this.users[i].user_id === user_id) {
                                        this.$delete(this.users, i);
                                    }
                                }
                            },
                            clearUsers: function () {
                                this.users = [];
                            },
                        },
                        components,
                    });
                })
                .then((app) => {
                    STUDIP.Blubber.Composer.vue = app;
                });
        },
    },
};

export default Blubber;
