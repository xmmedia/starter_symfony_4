import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createVfm } from 'vue-final-modal';
import { createHead } from '@unhead/vue';

import router from './user/router';
import { useRootStore } from '@/user/stores/root';

import { apolloClient } from './common/apollo';
import { provideApolloClient } from '@vue/apollo-composable';

import vFocus from './common/v_focus';

import * as Sentry from '@sentry/vue';

import App from './user/index';

import LoadingSpinner from './common/loading_spinner';
import FormError from './common/form_error';
import FieldError from './common/field_error';
import FieldPassword from './common/field_password';
import FormButton from './common/form_button.vue';
import PublicIcon from './common/public_icon';
import AdminModal from './common/modal';
import AdminDelete from './admin/admin_delete/index';
import LocalTime from './common/local_time';

import { MeQuery } from './user/queries/user.query.graphql';

// SASS/CSS
import '../../css/user.scss';

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
            });
            if (Me) {
                Sentry.setUser({ userId: Me.userId });
            }
        }

        app.use(router)
            .use(pinia)
            .use(createVfm())
            .use(createHead());

        app.directive('focus', vFocus);

        // global components
        app.component('LoadingSpinner', LoadingSpinner)
            .component('FormError', FormError)
            .component('FieldError', FieldError)
            .component('FieldPassword', FieldPassword)
            .component('FormButton', FormButton)
            .component('PublicIcon', PublicIcon)
            .component('AdminModal', AdminModal)
            .component('AdminDelete', AdminDelete)
            .component('LocalTime', LocalTime);

        app.mount('#app');
    });
