STUDIP.domReady(() => {
    const node = document.querySelector('.content-modules-vue-app');
    if (!node) {
        return;
    }

    Promise.all([
        STUDIP.Vue.load(),
        import('../../../vue/store/ContentModulesStore.js').then((config) => config.default),
        import('../../../vue/components/ContentModules.vue').then((component) => component.default),
    ]).then(([{ createApp, store }, storeConfig, ContentModules]) => {
        store.registerModule('contentmodules', storeConfig);

        Object.entries(window.ContentModulesStoreData ?? {}).forEach(([key, value]) => {
            store.commit(`contentmodules/${key}`, value);
        });

        const vm = createApp({
            components: { ContentModules }
        });
        vm.$mount(node);
    });
});

STUDIP.dialogReady(() => {
    const node = document.querySelector('.content-modules-controls-vue-app');
    if (!node) {
        return;
    }

    Promise.all([
        STUDIP.Vue.load(),
        import('../../../vue/components/ContentModulesControl.vue').then((component) => component.default),
    ]).then(([{ createApp }, ContentModulesControl]) => {
        const vm = createApp({
            components: { ContentModulesControl }
        });
        vm.$mount(node);
    });
});
