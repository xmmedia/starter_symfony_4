import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import PortalVue from 'portal-vue';
import { createVfm } from 'vue-final-modal';
import { createHead } from '@vueuse/head';

import router from './admin/router';
import { useRootStore } from '@/admin/stores/root';

import { apolloClient } from './common/apollo';
import { provideApolloClient } from '@vue/apollo-composable';

import * as Sentry from '@sentry/vue';

import App from './admin/index';

import LoadingSpinner from './common/loading_spinner';
import FormError from './common/form_error';
import FieldError from './common/field_error';
import FieldPassword from './common/field_password';
import AdminButton from './common/admin_button';
import AdminIcon from './common/admin_icon';

import { MeQuery } from './admin/queries/user.query.graphql';

// SASS/CSS
import '../../css/admin.scss';

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

        /*const entrypointScript = document.querySelector('script[data-entry="admin"]');
        if (entrypointScript && entrypointScript.integrity) {
            await rootStore.setIntegrityHash({
                entrypoint: 'admin',
                hash: entrypointScript.integrity,
            });
        }*/

        rootStore.ready();

        const app = createApp(App);

        if (import.meta.env.PROD) {
            Sentry.init({
                app,
                dsn: import.meta.env.VITE_SENTRY_DSN,
            });
        }

        app.use(router)
            .use(pinia)
            .use(PortalVue)
            .use(createVfm())
            .use(createHead());

        // global components
        app.component('LoadingSpinner', LoadingSpinner)
            .component('FormError', FormError)
            .component('FieldError', FieldError)
            .component('FieldPassword', FieldPassword)
            .component('AdminButton', AdminButton)
            .component('AdminIcon', AdminIcon)
            .component('AdminModal', defineAsyncComponent(() => import('./common/modal')))
            .component('AdminDelete', defineAsyncComponent(() => import('./admin/admin_delete/index')))
            .component('LocalTime', defineAsyncComponent(() => import('./common/local_time')));

        app.mount('#app');
    });
