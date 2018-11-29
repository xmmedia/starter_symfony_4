import VueRouter from 'vue-router';

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/recover',
            component: () => import('./user_recover/index'),
            children: [
                { path: '', redirect: '/recover/initiate' },
                {
                    name: 'user-recover-initiate',
                    path: 'initiate',
                    component: () => import('./user_recover/initiate'),
                },
                {
                    name: 'user-recover-reset',
                    path: 'reset/:token',
                    component: () => import('./user_recover/reset'),
                },
            ],
        },
        {
            name: 'user-verify',
            path: '/activate/:token',
            component: () => import('./user_verify/index'),
        },
        {
            path: '/profile/edit',
            component: () => import('./user_profile_edit/index'),
            children: [
                {
                    name: 'user-profile-edit',
                    path: '',
                    component: () => import('./user_profile_edit/profile'),
                },
                {
                    name: 'user-profile-edit-password',
                    path: 'password',
                    component: () => import('./user_profile_edit/password'),
                },
            ],
        },
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

    scrollBehavior (to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { x: 0, y: 0 };
        }
    },
});

export default router;
