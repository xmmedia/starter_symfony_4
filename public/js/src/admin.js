import { createApp, h, defineAsyncComponent } from 'vue';
import PortalVue from 'portal-vue';
// import VueModal from 'vue-js-modal';
import { createHead } from '@vueuse/head';

import router from './admin/router';
import store from './admin/store';
import apolloProvider from './common/apollo';

import app from './admin/index';

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

        window.App = createApp({
            render () {
                return h(app);
            },
        });

        window.App.use(router);
        window.App.use(store);
        window.App.use(apolloProvider);

        window.App.use(PortalVue);
        // window.App.use(VueModal, { componentName: 'vue-modal' });
        window.App.use(createHead());

        // global components
        window.App.component('loading-spinner', loadingSpinner);
        window.App.component('form-error', formError);
        window.App.component('field-error', fieldError);
        window.App.component('field-password', fieldPassword);
        window.App.component('admin-button', adminButton);
        window.App.component('admin-icon', adminIcon);
        window.App.component('admin-modal', defineAsyncComponent(() => import(/* webpackChunkName: "admin-modal" */ './common/modal')));
        window.App.component('admin-delete', defineAsyncComponent(() => import(/* webpackChunkName: "admin-delete" */ './admin/admin_delete/index')));
        window.App.component('local-time', defineAsyncComponent(() => import(/* webpackChunkName: "local-time" */ './common/local_time')));

        window.App.mount('#app');
    });
