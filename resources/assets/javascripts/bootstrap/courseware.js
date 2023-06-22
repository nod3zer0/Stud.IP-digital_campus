STUDIP.domReady(() => {
    if (document.getElementById('courseware-shelf-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-shelf-app" */
                '@/vue/courseware-shelf-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-shelf-app');
            });
        });
    }

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

    if (document.getElementById('courseware-activities-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-activities-app" */
                '@/vue/courseware-activities-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-activities-app');
            });
        });
    }

    if (document.getElementById('courseware-tasks-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-tasks-app" */
                '@/vue/courseware-tasks-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-tasks-app');
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

    if (document.getElementById('courseware-comments-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-comments-app" */
                '@/vue/courseware-comments-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-comments-app');
            });
        });
    }
});
