import VueRouter from 'vue-router';

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/admin/user',
            component: () => import('./user/index'),
            children: [
                {
                    name: 'admin-user',
                    path: '',
                    component: () => import('./user/list'),
                },
                {
                    name: 'admin-user-create',
                    path: 'create',
                    component: () => import('./user/create'),
                },
                {
                    name: 'admin-user-edit',
                    path: ':userId/edit',
                    component: () => import('./user/edit'),
                },
            ],
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
