export default {
    namespaced: true,

    state () {
        return {...STUDIP.config};
    },
    getters: {
        getConfig: (state) => (key) => {
            if (state[key] === undefined) {
                throw new Error(`Invalid access to unknown configuration item "${key}"`);
            }
            return state[key];
        }
    }
}
