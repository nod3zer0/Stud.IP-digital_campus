const StockImages = {
    init() {
        const stockImagesPage = document.querySelector('div.stock-images');
        if (stockImagesPage !== null) {
            Promise.all([window.STUDIP.Vue.load(), StockImages.plugin()]).then(
                ([{ Vue, createApp, store }, StockImagesPlugin]) => {
                    Vue.use(StockImagesPlugin, { store });
                    createApp({
                        el: stockImagesPage,
                        render: (h) => {
                            return h(Vue.component('StockImagesPage'), { props: {} });
                        },
                    });
                }
            );
        }
    },
    plugin() {
        return import('@/vue/plugins/stock-images.js').then(({ StockImagesPlugin }) => StockImagesPlugin);
    },
};

export default StockImages;
