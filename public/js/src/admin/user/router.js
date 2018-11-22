import VueRouter from 'vue-router';

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            name: 'admin-user-create',
            path: '/admin/user/create',
            component: null,
        },
        {
            name: 'admin-user-create',
            path: '/admin/user/create',
            component: null,
        },
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
