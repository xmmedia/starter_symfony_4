import { createRouter, createWebHistory } from 'vue-router';
import { scrollBehavior, parseQuery, stringifyQuery, logPageView, beforeEach } from '@/common/router_helpers';
import { useRootStore } from '@/user/stores/root';

const router = createRouter({
    history: createWebHistory(),

    routes: [
        // *********************************************************************
        // PUBLIC
        // *********************************************************************
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
                    path: 'reset',
                    component: () => import('./user_recover/reset'),
                },
            ],
        },
        {
            name: 'user-activate',
            path: '/activate',
            component: () => import('./user_activate'),
        },
        {
            name: 'user-verify',
            path: '/verify',
            component: () => import('./user_verify'),
        },

        // *********************************************************************
        // LOGGED IN
        // *********************************************************************
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('./dashboard'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_USER',
            },
        },
        {
            path: '/profile',
            component: () => import('./profile_edit'),
            redirect: '/profile/edit',
            children: [
                {
                    name: 'user-profile-edit',
                    path: 'edit',
                    component: () => import('./profile_edit/profile'),
                },
                {
                    name: 'user-profile-edit-password',
                    path: 'password',
                    component: () => import('./profile_edit/password'),
                },
            ],
            meta: {
                requiresAuth: true,
                role: 'ROLE_USER',
            },
        },

        // *********************************************************************
        // ERRORS
        // *********************************************************************
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

router.beforeEach(beforeEach(router.resolve({ name: 'login' }).href, useRootStore, 'user'));

router.afterEach((to) => {
    logPageView(to);
});

export default router;
