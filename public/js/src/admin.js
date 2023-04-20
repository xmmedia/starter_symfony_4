import { createApp, defineAsyncComponent } from 'vue';
import PortalVue from 'portal-vue';
import { createVfm } from 'vue-final-modal';
import { createHead } from '@vueuse/head';

import router from './admin/router';
import store from './admin/store';
import apolloProvider from './common/apollo';

import appIndex from './admin/index';

import loadingSpinner from './common/loading_spinner';
import formError from './common/form_error';
import fieldError from './common/field_error';
import fieldPassword from './common/field_password';
import adminButton from './common/admin_button';
import adminIcon from './common/admin_icon';

import { MeQuery } from './admin/queries/user.query.graphql';

// SASS/CSS
import '../../css/admin.scss';

// run gql query to see if the user is logged in, set state to ready
// and then initialize
apolloProvider.defaultClient.query({ query: MeQuery })
    .then(async (result) =>  {
        // don't set a user if we didn't get anything
        if (result.data.Me) {
            await store.dispatch('updateUser', result.data.Me);
        }

        const entrypointScript = document.querySelector('script[data-entry="admin"]');
        if (entrypointScript && entrypointScript.integrity) {
            await store.dispatch('setIntegrityHash', {
                entrypoint: 'admin',
                hash: entrypointScript.integrity,
            });
        }

        store.commit('ready');

        const app = createApp(appIndex);

        app.use(router);
        app.use(store);
        app.use(apolloProvider);

        app.use(PortalVue);
        app.use(createVfm());
        app.use(createHead());

        // global components
        app.component('loading-spinner', loadingSpinner);
        app.component('form-error', formError);
        app.component('field-error', fieldError);
        app.component('field-password', fieldPassword);
        app.component('admin-button', adminButton);
        app.component('admin-icon', adminIcon);
        app.component('admin-modal', defineAsyncComponent(() => import('./common/modal')));
        app.component('admin-delete', defineAsyncComponent(() => import('./admin/admin_delete/index')));
        app.component('local-time', defineAsyncComponent(() => import('./common/local_time')));

        app.mount('#app');
    });
