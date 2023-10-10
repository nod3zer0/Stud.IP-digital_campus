STUDIP.domReady(() => {
    const node = document.querySelector('.admin-courses-vue-app');
    if (!node) {
        return;
    }

    Promise.all([
        STUDIP.Vue.load(),
        import('../../../vue/store/AdminCoursesStore.js').then((config) => config.default),
        import('../../../vue/components/AdminCourses.vue').then((component) => component.default),
    ]).then(([{ createApp, store }, storeConfig, AdminCourses]) => {
        store.registerModule('admincourses', storeConfig);

        Object.entries(window.AdminCoursesStoreData ?? {}).forEach(([key, value]) => {
            store.commit(`admincourses/${key}`, value);
        })

        const vm = createApp({
            components: { AdminCourses },
        });
        vm.$mount(node);

        STUDIP.AdminCourses.App = vm.$refs.app;
    });



    $('.admin-courses-options').find('.options-radio, .options-checkbox').on('click', function () {
        $(this).toggleClass(['options-checked', 'options-unchecked']);
        $(this).attr('aria-checked', $(this).is('.options-checked') ? 'true' : 'false');

        if ($(this).is('.options-radio')) {
            const filterName = $(this).data('filter-name');
            $(`button[data-filter-name="${filterName}"]`)
                .not(this)
                .removeClass('options-checked')
                .addClass('options-unchecked')
                .attr('aria-checked', 'false');
        }
    });
});
