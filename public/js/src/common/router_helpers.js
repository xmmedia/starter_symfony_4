import qs from 'qs';
import { apolloClient } from '@/common/apollo';
import { RouteQuery, EntrypointIntegrityQuery } from '@/common/queries/route.query.graphql';

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

const buildHasChanged = function (loadedHash, currentHash) {
    return !!loadedHash && !!currentHash && loadedHash !== currentHash;
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
                window.location = loginUrl + '?_target_path=' + encodeURIComponent(to.fullPath);

                return false;
            }

            // if they've been signed out, Me is denied & sessionLink holds this until they sign back in
            const result = await apolloClient.query({
                query: RouteQuery,
                variables: {
                    entrypoint: integrityHashKey,
                },
            });

            // JS files have changed, so load the target page fresh
            // (prefixed with the origin so a path starting with // can't go to another site)
            const loadedHash = rootStore.entrypointIntegrityHashes[integrityHashKey];
            if (buildHasChanged(loadedHash, result.data.EntrypointIntegrity)) {
                window.location = window.location.origin + to.fullPath;

                return false;
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

// a dynamic import fails when a deploy has removed the chunk, so reload if the JS has changed
// (a failed request, such as when offline, leaves the page as is)
// several imports can fail at once (such as a chunk & its CSS), so only one check runs at a time
export const reloadOnChangedBuild = function (useRootStore, integrityHashKey) {
    let checking = false;

    window.addEventListener('vite:preloadError', async () => {
        if (checking) {
            return;
        }
        checking = true;

        try {
            const { data } = await apolloClient.query({
                query: EntrypointIntegrityQuery,
                variables: {
                    entrypoint: integrityHashKey,
                },
            });
            const loadedHash = useRootStore().entrypointIntegrityHashes[integrityHashKey];
            if (buildHasChanged(loadedHash, data.EntrypointIntegrity)) {
                window.location.reload();
            }
        } catch {
            // the import failure is already reported, so there's nothing more to do
        } finally {
            checking = false;
        }
    });
};
