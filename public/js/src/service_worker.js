/*global workbox*/
// to enable debugging in dev
// workbox.setConfig({
//     debug: true,
// });

// these calls encourage the ServiceWorkers to get in there fast
// and not allow any straggling "old" SWs to hang around
workbox.core.skipWaiting();
workbox.core.clientsClaim();

// cache everything in the manifest
workbox.precaching.precacheAndRoute(self.__precacheManifest);

// enable Google Analytics while offline
// will be sent once online
workbox.googleAnalytics.initialize();

// not entirely sure this is needed,
// but without it, the browser detects that they're offline
// workbox.routing.setDefaultHandler(async ({ url, event }) => {
//     return await fetch(event.request);
// });

// show /offline when the previous document (navigation) results in an error
// both this and the default handler need to be enabled
// based on: https://developers.google.com/web/tools/workbox/guides/advanced-recipes#provide_a_fallback_response_to_a_route
// & https://github.com/GoogleChrome/workbox/issues/1729#issuecomment-431908228
// workbox.routing.setCatchHandler(async ({ url, event }) => {
//     switch (event.request.destination) {
//         case 'document':
//             return caches.match('/offline', { ignoreSearch: true });
//
//         default:
//             // If we don't have a fallback, just return an error response.
//             return Response.error();
//     }
// });

workbox.routing.registerRoute(/\.(?:js|css|svg|png|jpg|gif|ico)$/,
    new workbox.strategies.NetworkFirst({
        cacheName: 'static-resources',
        networkTimeoutSeconds: 5,
        plugins: [],
    }),
    'GET');

workbox.routing.registerRoute(/https:\/\/fonts.(?:googleapis|gstatic).com\/(.*)/,
    new workbox.strategies.CacheFirst({
        cacheName: 'googleapis',
        plugins: [
            new workbox.expiration.Plugin({
                maxEntries: 30,
                purgeOnQuotaError: false,
            })],
    }),
    'GET');
