import axios from 'axios';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import stockImagesModule from '../store/stock-images.js';
import * as components from '../components/stock-images/components.js';

const JSONAPI_PATH = 'jsonapi.php/v1';

export const StockImagesPlugin = {
    install(Vue, options = {}) {
        if (!('store' in options)) {
            throw new Error('You must provide the vuex store via the options argument');
        }
        this.enhanceStore(options.store);
        this.registerComponents(Vue);
    },
    enhanceStore(store) {
        const httpClient = getHttpClient(window.STUDIP.URLHelper.getURL(JSONAPI_PATH, {}, true));
        initializeStore(store, httpClient);
    },
    registerComponents(Vue) {
        Object.entries(components).forEach(([name, component]) => {
            const exists = Vue.component(name);
            if (!exists) {
                Vue.component(name, component);
            }
        });
    },
};

function getHttpClient(baseURL) {
    return axios.create({ baseURL, headers: { 'Content-Type': 'application/vnd.api+json' } });
}

function initializeStore(store, httpClient) {
    const modules = mapResourceModules({ names: ['stock-images'], httpClient });
    Object.entries(modules).forEach(([name, module]) => {
        if (!store.hasModule(name)) {
            store.registerModule(name, module);
        }
    });
    if (!store.hasModule(['studip'])) {
        store.registerModule(['studip'], { namespaced: true });
    }

    if (!store.hasModule(['studip', 'stockImages'])) {
        store.registerModule(['studip', 'stockImages'], stockImagesModule);
        store.commit('studip/stockImages/setHttpClient', httpClient);
    }
}
