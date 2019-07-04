import Vue from 'vue';
import Router from 'vue-router';
import store from './store';
import apolloProvider from '@/common/apollo';

import qs from 'qs';

import { MeSimpleQuery } from '@/admin/queries/user.query';

Vue.use(Router);

const router = new Router({
    mode: 'history',

    routes: [
        {
            name: 'login',
            path: '/login',
            component: () => import(/* webpackChunkName: "login" */ './login'),
        },
        {
            path: '/recover',
            component: () => import(/* webpackChunkName: "user-recover" */ './user_recover'),
            children: [
                { path: '', redirect: '/recover/initiate' },
                {
                    name: 'user-recover-initiate',
                    path: 'initiate',
                    component: () => import(/* webpackChunkName: "user-recover" */ './user_recover/initiate'),
                },
                {
                    name: 'user-recover-reset',
                    path: 'reset/:token',
                    component: () => import(/* webpackChunkName: "user-recover" */ './user_recover/reset'),
                },
            ],
        },
        {
            name: 'user-verify',
            path: '/activate/:token',
            component: () => import(/* webpackChunkName: "user-verify" */ './user_verify'),
        },
        {
            path: '/profile/edit',
            component: () => import(/* webpackChunkName: "user-profile-edit" */ './user_profile_edit'),
            children: [
                {
                    name: 'user-profile-edit',
                    path: '',
                    component: () => import(/* webpackChunkName: "user-profile-edit" */ './user_profile_edit/profile'),
                },
                {
                    name: 'user-profile-edit-password',
                    path: 'password',
                    component: () => import(/* webpackChunkName: "user-profile-edit" */ './user_profile_edit/password'),
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
            component: () => import(/* webpackChunkName: "admin-dashboard" */ './admin_dashboard'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_ADMIN',
            },
        },
        {
            path: '/admin/user',
            component: () => import(/* webpackChunkName: "admin-user" */ './user'),
            children: [
                {
                    name: 'admin-user',
                    path: '',
                    component: () => import(/* webpackChunkName: "admin-user" */ './user/list'),
                },
                {
                    name: 'admin-user-add',
                    path: 'add',
                    component: () => import(/* webpackChunkName: "admin-user" */ './user/add'),
                },
                {
                    name: 'admin-user-edit',
                    path: ':userId/edit',
                    component: () => import(/* webpackChunkName: "admin-user" */ './user/edit'),
                    props: true,
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
            component: () => import(/* webpackChunkName: "admin-pattern_library" */ './pattern_library'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_SUPER_ADMIN',
            },
        },

        {
            path: '*',
            name: '404',
            component: () => import(/* webpackChunkName: "admin-404" */ './error/404.vue'),
        },
    ],

    scrollBehavior (to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { x: 0, y: 0 };
        }
    },

    // set custom query resolver
    parseQuery (query) {
        return qs.parse(query);
    },
    // set custom query stringifier
    stringifyQuery (query) {
        const result = qs.stringify(query);

        return result ? ('?' + result) : '';
    },
});

router.beforeEach( async (to, from, next) => {
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

    if (requiresAuth) {
        // check to see if they're still authenticated
        const result = await apolloProvider.defaultClient.query({
            query: MeSimpleQuery,
            fetchPolicy: 'no-cache',
        });
        if (!result.data.Me) {
            window.location = router.resolve({ name: 'login' }).href;

            return;
        }

        // find the first matched route that has a role
        const routeWithRole = to.matched.find(record => record.meta && record.meta.role);

        // this route requires auth, check if logged in
        // and check if they have the right role
        if (store.getters.loggedIn && store.getters.hasRole(routeWithRole.meta.role)) {
            next();
        } else {
            next({ name: 'login' });
        }
    } else {
        next();
    }
});

export default router;
