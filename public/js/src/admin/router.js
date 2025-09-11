import { createRouter, createWebHistory } from 'vue-router';
import { useRootStore } from './stores/root';
import { scrollBehaviour as scrollBehavior, parseQuery, stringifyQuery, logPageView, beforeEach } from '@/common/router_helpers';

const router = createRouter({
    history: createWebHistory(),

    routes: [
        {
            name: 'admin-dashboard',
            path: '/admin',
            component: () => import('./admin_dashboard/index.vue'),
            meta: {
                requiresAuth: true,
                role: 'ROLE_ADMIN',
            },
        },
        {
            path: '/admin/user',
            component: () => import('./user/index.vue'),
            children: [
                {
                    name: 'admin-user',
                    path: '',
                    component: () => import('./user/list.vue'),
                },
                {
                    name: 'admin-user-view',
                    path: ':userId/view',
                    component: () => import('./user/view.vue'),
                    props: true,
                },
                {
                    name: 'admin-user-add',
                    path: 'add',
                    component: () => import('./user/add.vue'),
                },
                {
                    name: 'admin-user-edit',
                    path: ':userId/edit',
                    component: () => import('./user/edit.vue'),
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

router.beforeEach(beforeEach('/login', useRootStore, 'admin'));

router.afterEach((to) => {
    logPageView(to);
});

export default router;
