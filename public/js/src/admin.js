import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import PortalVue from 'portal-vue';
import { createVfm } from 'vue-final-modal';
import { createHead } from '@vueuse/head';

import router from './admin/router';
import { useRootStore } from '@/admin/stores/root';

import { apolloClient } from './common/apollo';
import { provideApolloClient } from '@vue/apollo-composable';

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
        app.use(router);
        app.use(pinia);
        app.use(PortalVue);
        app.use(createVfm());
        app.use(createHead());

        // app.provide(DefaultApolloClient, apolloClient);

        // global components
        app.component('LoadingSpinner', LoadingSpinner);
        app.component('FormError', FormError);
        app.component('FieldError', FieldError);
        app.component('FieldPassword', FieldPassword);
        app.component('AdminButton', AdminButton);
        app.component('AdminIcon', AdminIcon);
        app.component('AdminModal', defineAsyncComponent(() => import('./common/modal')));
        app.component('AdminDelete', defineAsyncComponent(() => import('./admin/admin_delete/index')));
        app.component('LocalTime', defineAsyncComponent(() => import('./common/local_time')));

        app.mount('#app');
    });
