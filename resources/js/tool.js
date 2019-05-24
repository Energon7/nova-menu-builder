import ToggleButton from 'vue-js-toggle-button';

Nova.booting((Vue, router) => {
    router.addRoutes([
        {
            name: 'menu-builder',
            path: '/menu-builder',
            component: require('./components/Tool'),
        },
    ]);

    Vue.use(ToggleButton);
    Vue.component('builder-resource-tool', require('./components/BuilderResourceTool'));
});
