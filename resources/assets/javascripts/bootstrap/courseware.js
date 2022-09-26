STUDIP.domReady(() => {
    if (document.getElementById('courseware-index-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-index-app" */
                '@/vue/courseware-index-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-index-app');
            });
        });
    }

    if (document.getElementById('courseware-dashboard-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-dashboard-app" */
                '@/vue/courseware-dashboard-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-dashboard-app');
            });
        });
    }

    if (document.getElementById('courseware-manager-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-manager-app" */
                '@/vue/courseware-manager-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-manager-app');
            });
        });
    }

    if (document.getElementById('courseware-content-overview-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-content-overview-app" */
                '@/vue/courseware-content-overview-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-content-overview-app');
            });
        });
    }

    if (document.getElementById('courseware-content-bookmark-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-content-bookmark-app" */
                '@/vue/courseware-content-bookmark-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-content-bookmark-app');
            });
        });
    }

    if (document.getElementById('courseware-admin-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-content-bookmark-app" */
                '@/vue/courseware-admin-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-admin-app');
            });
        });
    }

    if (document.getElementById('courseware-public-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-public-app" */
                '@/vue/courseware-public-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-public-app');
            });
        });
    }

    if (document.getElementById('courseware-content-releases-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-content-releases-app" */
                '@/vue/courseware-content-releases-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-content-releases-app');
            });
        });
    }
});
