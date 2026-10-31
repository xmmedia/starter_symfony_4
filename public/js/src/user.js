import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createHead } from '@unhead/vue/client';
import { logError } from '@/common/lib';

import router from './user/router';
import { useRootStore } from '@/user/stores/root';

import { apolloClient } from './common/apollo';
import { provideApolloClient } from '@vue/apollo-composable';
import { setSessionStore } from './common/session';

import vFocus from './common/v_focus';

import { initSentry } from './common/sentry';

import App from './user/index.vue';

import LoadingSpinner from './common/loading_spinner.vue';
import FormError from './common/form_error.vue';
import FieldError from './common/field_error.vue';
import FormButton from './common/form_button.vue';
import PublicIcon from './common/public_icon.vue';
import UserModal from './common/modal.vue';
import LocalTime from './common/local_time.vue';

import { MeQuery } from './user/queries/user.query.graphql';
import { reloadOnChangedBuild } from './common/router_helpers';

// CSS
import '../../css/user.css';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
initSentry(app, pinia, useRootStore);

const rootStore = useRootStore(pinia);
setSessionStore(rootStore);

reloadOnChangedBuild(useRootStore, 'user');

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

        app.use(router)
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
    .catch((e) => {
        logError(e);
        alert('There was an error loading the page. The page is not working as expected. Please refresh the page.');
    });
