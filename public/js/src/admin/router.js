import Vue from 'vue';
import Router from 'vue-router';
import store from './store';
import apolloProvider from '@/common/apollo';
import { scrollBehavior, parseQuery, stringifyQuery, logPageView } from '@/common/router_helpers';

import { MeSimpleQuery } from './queries/user.query.graphql';

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
            component: () => import(/* webpackChunkName: "admin-error" */ './error/404'),
        },
        {
            path: '*',
            name: '403',
            component: () => import(/* webpackChunkName: "admin-error" */ './error/403'),
        },
    ],

    scrollBehavior,
    parseQuery,
    stringifyQuery,
});

router.beforeEach( async (to, from, next) => {
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

    if (requiresAuth) {
        if (!store.getters.loggedIn) {
            window.location = router.resolve({ name: 'login' }).href;

            return;
        }

        const waitForApollo = function () {
            return new Promise((resolve) => {
                (function waitingForApollo (){
                    if (router.app.$apolloProvider) {
                        return resolve();
                    }

                    setTimeout(waitingForApollo, 10);
                })();
            });
        };

        // check if Apollo is setup on the Vue instance
        await waitForApollo();

        // check to see if they're still authenticated
        const result = await router.app.$apollo.query({
            query: MeSimpleQuery,
        });
        if (!result.data.Me) {
            window.location = router.resolve({ name: 'login' }).href;

            return;
        }

        // find the first matched route that has a role
        const routeWithRole = to.matched.find(record => record.meta && record.meta.role);

        // this route requires auth, therefore check if they have the right role
        if (store.getters.hasRole(routeWithRole.meta.role)) {
            next();
        } else {
            next({ name: '403' });
        }
    } else {
        next();
    }
});

router.afterEach((to) => {
    logPageView(to);
});

export default router;
