import qs from 'qs';
import { apolloClient } from '@/common/apollo';
import { RouteQuery } from '@/common/queries/route.query.graphql';

export const scrollBehaviour = function (to, from, savedPosition) {
    if (savedPosition) {
        return savedPosition;
    } else {
        return { top: 0 };
    }
};

// set custom query resolver
export const parseQuery = function (query) {
    return qs.parse(query);
};

// set custom query stringifier
export const stringifyQuery = function (query) {
    return qs.stringify(query);
};

/*global gtag*/
export const logPageView = function (to) {
    if (window.gtag) {
        gtag('event', 'page_view', {
            page_location: window.location.origin + to.fullPath,
        });
    }
};

export const beforeEach = function (loginUrl, useRootStore, integrityHashKey) {
    return async (to, from) => {
        // don't do any checks if we're staying on the same route
        // this is most likely when changing the query string
        if (typeof to.name !== 'undefined' && typeof from.name !== 'undefined' && to.name === from.name) {
            return;
        }

        const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
        const rootStore = useRootStore();

        if (requiresAuth) {
            if (!rootStore.loggedIn) {
                window.location = loginUrl + '?_target_path=' + window.location.href;

                return false;
            }

            // check to see if they're still authenticated
            const result = await apolloClient.query({
                query: RouteQuery,
                variables: {
                    entrypoint: integrityHashKey,
                },
            });
            if (!result.data.Me) {
                window.location = loginUrl + '?_target_path=' + window.location.href;

                return false;
            }

            // JS files have changed
            if (result.data.EntrypointIntegrity && rootStore.entrypointIntegrityHashes[integrityHashKey]) {
                if (result.data.EntrypointIntegrity !== rootStore.entrypointIntegrityHashes[integrityHashKey]) {
                    window.location.reload();

                    return false;
                }
            }

            // find the first matched route that has a role
            const routeWithRole = to.matched.find(record => !!record.meta?.role);

            // this route requires auth, therefore check if they have the right role
            if (!routeWithRole || !rootStore.hasRole(routeWithRole.meta.role)) {
                return { name: '403' };
            }
        }
    };
}
