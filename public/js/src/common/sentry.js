import * as Sentry from '@sentry/vue';
import { watch } from 'vue';

// a JS or CSS file failed to load: Chrome, Firefox & Safari's messages for a dynamic import, & Vite's
// for a CSS preload
const fileLoadFailure = new RegExp([
    'Failed to fetch dynamically imported module',
    'error loading dynamically imported module',
    'Importing a module script failed',
    'Unable to preload CSS',
].join('|'));

// a lost connection causes a steady trickle of these, but a lot at once can mean a broken deploy,
// so they're grouped into one issue as warnings, for a Sentry alert on the number of events
const groupFileLoadFailures = function (event) {
    const message = event.exception?.values?.at(-1)?.value;
    if (message && fileLoadFailure.test(message)) {
        event.fingerprint = ['file-load-failure'];
        event.level = 'warning';
    }

    return event;
};

// call before any store is created: Pinia only applies plugins to stores created after them
export const initSentry = function (app, pinia, useRootStore) {
    if (!import.meta.env.PROD || !import.meta.env.VITE_SENTRY_DSN) {
        return;
    }

    // runs before the app's error handling is set up, so Sentry failing mustn't stop the app loading
    try {
        // for performance tracing, pass the router in from admin.js/user.js, then add
        // Sentry.browserTracingIntegration({ router }) & a tracesSampleRate
        Sentry.init({
            app,
            dsn: import.meta.env.VITE_SENTRY_DSN,
            environment: import.meta.env.VITE_SENTRY_ENVIRONMENT,
            release: import.meta.env.VITE_SENTRY_RELEASE,
            // the props of the component an error happened in aren't filtered, eg the password
            // in field_password & password_score
            attachProps: false,
            // a fetch() couldn't reach the server (offline, waking from sleep, blocked): Chrome,
            // Firefox & Safari's messages. Anchored, so failing to load a JS file (eg Chrome's "Failed
            // to fetch dynamically imported module"), which can mean a broken deploy, is still sent
            ignoreErrors: [/^Failed to fetch$/, /^NetworkError when attempting to fetch resource\.$/, /^Load failed$/],
            beforeSend: groupFileLoadFailures,
        });

        pinia.use(Sentry.createSentryPiniaPlugin());

        const rootStore = useRootStore(pinia);
        watch(
            () => rootStore.user?.userId,
            (userId) => Sentry.setUser(userId ? { id: userId } : null),
            { immediate: true },
        );
    } catch (e) {
        // not logError(): it sends to Sentry
        // eslint-disable-next-line no-console
        console.error(e);
    }
};
