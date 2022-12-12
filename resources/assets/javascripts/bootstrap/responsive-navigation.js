import ResponsiveNavigation from '../../../vue/components/responsive/ResponsiveNavigation.vue';

STUDIP.ready(() => {
    STUDIP.Vue.load().then(({ createApp }) => {
        createApp({
            el: '#responsive-menu',
            components: { ResponsiveNavigation }
        });
    });
});
