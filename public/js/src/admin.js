import { createApp, defineAsyncComponent } from 'vue';
import PortalVue from 'portal-vue';
import { createVfm } from 'vue-final-modal';
import { createHead } from '@vueuse/head';

import router from './admin/router';
import store from './admin/store';

import { apolloClient } from './common/apollo';
import { provideApolloClient } from '@vue/apollo-composable';

import appIndex from './admin/index';

import LoadingSpinner from './common/loading_spinner';
import FormError from './common/form_error';
import FieldError from './common/field_error';
import FieldPassword from './common/field_password';
import AdminButton from './common/admin_button';
import AdminIcon from './common/admin_icon';

import { MeQuery } from './admin/queries/user.query.graphql';

// SASS/CSS
import '../../css/admin.scss';

provideApolloClient(apolloClient);

// run gql query to see if the user is logged in, set state to ready
// and then initialize
apolloClient.query({ query: MeQuery })
    .then(async (result) =>  {
        // don't set a user if we didn't get anything
        if (result.data.Me) {
            await store.dispatch('updateUser', result.data.Me);
        }

        /*const entrypointScript = document.querySelector('script[data-entry="admin"]');
        if (entrypointScript && entrypointScript.integrity) {
            await store.dispatch('setIntegrityHash', {
                entrypoint: 'admin',
                hash: entrypointScript.integrity,
            });
        }*/

        store.commit('ready');

        const app = createApp(appIndex);

        app.use(router);
        app.use(store);

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
