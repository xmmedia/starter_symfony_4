import { createRouter, createWebHistory } from 'vue-router';
import { useRootStore } from './stores/root';
import { scrollBehavior, parseQuery, stringifyQuery, logPageView } from '@/common/router_helpers';
import { apolloClient } from '@/common/apollo';

import { RouteQuery } from './queries/user.query.graphql';

const router = createRouter({
    history: createWebHistory(),

    routes: [
        {
            name: 'login',
            path: '/login',
            component: () => import('./login'),
        },
        {
            path: '/recover',
            component: () => import('./user_recover'),
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
            component: () => import('./user_verify'),
        },
        {
            path: '/profile/edit',
            component: () => import('./user_profile_edit'),
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
            component: () => import('./admin_dashboard'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_ADMIN',
            },
        },
        {
            path: '/admin/user',
            component: () => import('./user'),
            children: [
                {
                    name: 'admin-user',
                    path: '',
                    component: () => import('./user/list'),
                },
                {
                    name: 'admin-user-add',
                    path: 'add',
                    component: () => import('./user/add'),
                },
                {
                    name: 'admin-user-edit',
                    path: ':userId/edit',
                    component: () => import('./user/edit'),
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
            component: () => import('./pattern_library'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_SUPER_ADMIN',
            },
        },

        {
            path: '/:pathMatch(.*)*',
            name: '404',
            component: () => import('./error/404'),
        },
        {
            path: '/:pathMatch(.*)*',
            name: '403',
            component: () => import('./error/403'),
        },
    ],

    scrollBehavior,
    parseQuery,
    stringifyQuery,
});

router.beforeEach( async (to, from, next) => {
    // don't do any checks if we're staying on the same route
    // this is most likely when changing the query string
    if (typeof to.name !== 'undefined' && typeof from.name !== 'undefined' && to.name === from.name) {
        next();

        return;
    }

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const rootStore = useRootStore();

    if (requiresAuth) {
        if (!rootStore.loggedIn) {
            window.location = router.resolve({ name: 'login' }).href;

            return;
        }

        // check to see if they're still authenticated
        const result = await apolloClient.query({
            query: RouteQuery,
        });
        if (!result.data.Me) {
            window.location = router.resolve({ name: 'login' }).href;

            return;
        }

        // JS files have changed
        /*if (result.data.EntrypointIntegrity !== rootStore.entrypointIntegrityHashes.admin) {
            if (result.data.EntrypointIntegrity && rootStore.entrypointIntegrityHashes.admin) {
                window.location.reload();

                return;
            }
        }*/

        // find the first matched route that has a role
        const routeWithRole = to.matched.find(record => record.meta && record.meta.role);

        // this route requires auth, therefore check if they have the right role
        if (rootStore.hasRole(routeWithRole.meta.role)) {
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
