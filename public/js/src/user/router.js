import { createRouter, createWebHistory } from 'vue-router';
import { scrollBehaviour as scrollBehavior, parseQuery, stringifyQuery, logPageView, beforeEach } from '@/common/router_helpers';
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
            component: () => import('./login/index.vue'),
        },
        {
            path: '/recover',
            component: () => import('./user_recover/index.vue'),
            children: [
                { path: '', redirect: '/recover/initiate' },
                {
                    name: 'user-recover-initiate',
                    path: 'initiate',
                    component: () => import('./user_recover/initiate.vue'),
                },
                {
                    name: 'user-recover-reset',
                    path: 'reset',
                    component: () => import('./user_recover/reset.vue'),
                },
            ],
        },
        {
            name: 'user-activate',
            path: '/activate',
            component: () => import('./user_activate/index.vue'),
        },
        {
            name: 'user-verify',
            path: '/verify',
            component: () => import('./user_verify/index.vue'),
        },

        // *********************************************************************
        // LOGGED IN
        // *********************************************************************
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('./dashboard/index.vue'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_USER',
            },
        },
        {
            path: '/profile',
            component: () => import('./profile_edit/index.vue'),
            redirect: '/profile/edit',
            children: [
                {
                    name: 'user-profile-edit',
                    path: 'edit',
                    component: () => import('./profile_edit/profile.vue'),
                },
                {
                    name: 'user-profile-edit-password',
                    path: 'password',
                    component: () => import('./profile_edit/password.vue'),
                },
            ],
            meta: {
                requiresAuth: true,
                role: 'ROLE_USER',
            },
        },

        {
            path: '/profile/passkeys',
            redirect: '/profile/security/2fa',
        },
        {
            path: '/profile/security',
            component: () => import('./profile_security/index.vue'),
            redirect: '/profile/security/2fa',
            children: [
                {
                    name: 'user-profile-security',
                    path: '2fa',
                    component: () => import('./profile_security/security.vue'),
                },
            ],
            meta: {
                requiresAuth: true,
                role: 'ROLE_USER',
            },
        },
        {
            name: '2fa',
            path: '/2fa',
            component: () => import('./two_factor/index.vue'),
        },

        // *********************************************************************
        // ERRORS
        // *********************************************************************
        {
            path: '/:pathMatch(.*)*',
            name: '404',
            component: () => import('./error/404.vue'),
        },
        {
            path: '/:pathMatch(.*)*',
            name: '403',
            component: () => import('./error/403.vue'),
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
