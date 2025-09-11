import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createHead } from '@unhead/vue/client';

import router from './user/router';
import { useRootStore } from '@/user/stores/root';

import { apolloClient } from './common/apollo';
import { provideApolloClient } from '@vue/apollo-composable';

import vFocus from './common/v_focus';

import * as Sentry from '@sentry/vue';

import App from './user/index.vue';

import LoadingSpinner from './common/loading_spinner.vue';
import FormError from './common/form_error.vue';
import FieldError from './common/field_error.vue';
import FormButton from './common/form_button.vue';
import PublicIcon from './common/public_icon.vue';
import UserModal from './common/modal.vue';
import LocalTime from './common/local_time.vue';

import { MeQuery } from './user/queries/user.query.graphql';

// SASS/CSS
import '../../css/user.css';

const pinia = createPinia();
const rootStore = useRootStore(pinia);

provideApolloClient(apolloClient);

// run gql query to see if the user is logged in, set state to ready
// and then initialize
apolloClient.query({ query: MeQuery })
    .then(async ({ data: { Me }}) =>  {
        // don't set a user if we didn't get anything
        if (Me) {
            rootStore.updateUser(Me);
        }

        const entrypointScript = document.querySelector('script[data-entry="user"]');
        if (entrypointScript && entrypointScript.integrity) {
            await rootStore.setIntegrityHash({
                entrypoint: 'user',
                hash: entrypointScript.integrity,
            });
        }

        rootStore.ready();

        const app = createApp(App);

        if (import.meta.env.PROD) {
            Sentry.init({
                app,
                dsn: import.meta.env.VITE_SENTRY_DSN,
                sendDefaultPii: true,
                integrations: [
                    Sentry.browserTracingIntegration({ router }),
                ],
            });
            if (rootStore.user) {
                Sentry.setUser({ userId: rootStore.user.userId });
            }

            pinia.use(Sentry.createSentryPiniaPlugin());
        }

        app.use(router)
            .use(pinia)
            .use(createHead());

        app.directive('focus', vFocus);

        // global components
        app.component('LoadingSpinner', LoadingSpinner)
            .component('FormError', FormError)
            .component('FieldError', FieldError)
            .component('FormButton', FormButton)
            .component('PublicIcon', PublicIcon)
            .component('UserModal', UserModal)
            .component('LocalTime', LocalTime);

        app.mount('#app');
    })
    .catch(() => {
        alert('There was an error loading the page. The page is not working as expected. Please refresh the page.');

    });
