import StudipTree from '../../../vue/components/tree/StudipTree.vue'

STUDIP.ready(() => {
    document.querySelectorAll('[data-studip-tree]').forEach(element => {
        STUDIP.Vue.load().then(({ createApp }) => {
            createApp({
                el: element,
                components: { StudipTree }
            })
        })
    });
});
