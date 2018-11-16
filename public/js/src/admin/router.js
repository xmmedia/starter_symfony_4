import VueRouter from 'vue-router';

const router = new VueRouter({
    mode: 'history',
    routes: [
        // {
        //     name: 'dashboard',
        //     path: '/',
        //     component: () => import('./dashboard/index'),
        // },
    ],

    // scrollBehavior (to, from, savedPosition) {
    //     if (savedPosition) {
    //         return savedPosition;
    //     } else {
    //         return {
    //             selector: '.class',
    //             offset: { x: 0, y: -10 },
    //         };
    //     }
    // }
});

export default router;
