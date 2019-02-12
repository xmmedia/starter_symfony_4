import VueRouter from 'vue-router';
import store from './store';

const router = new VueRouter({
    mode: 'history',

    routes: [
        {
            name: 'login',
            path: '/login',
            component: () => import('./login'),
        },
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
            meta: {
                requiresAuth: true,
                role: 'ROLE_USER',
            },
        },
        {
            name: 'admin-dashboard',
            path: '/admin',
            component: () => import('./admin_dashboard/index'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_ADMIN',
            },
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
            meta: {
                requiresAuth: true,
                role: 'ROLE_ADMIN',
            },
        },

        {
            path: '/admin/pattern-library',
            name: 'pattern-library',
            component: () => import('./pattern_library/index'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_SUPER_ADMIN',
            },
        },

        {
            path: '*',
            name: '404',
            component: () => import('./error/404.vue'),
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

router.beforeEach( (to, from, next) => {
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

    if (requiresAuth) {
        // this route requires auth, check if logged in
        // and check if they have the right role
        if (store.getters.loggedIn && store.getters.hasRole(to.meta.role)) {
            next();
        } else {
            next({ name: 'login' });
        }
    } else {
        next();
    }
});

export default router;
