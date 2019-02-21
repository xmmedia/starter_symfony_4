import Vue from 'vue';
import PortalVue from 'portal-vue';
import VueModal from 'vue-js-modal';

import router from './admin/router';
import store from './admin/store';
import apolloProvider from './common/apollo';

import app from './admin/index';

import fieldErrors from './common/field_errors';
import passwordField from './common/password_field';
import { MeQuery } from './admin/queries/user.query';

// SASS/CSS
import '../../css/sass/admin.scss';

// disable the warning about dev/prod
Vue.config.productionTip = false;

Vue.use(PortalVue);
Vue.use(VueModal);

// global components
Vue.component('field-errors', fieldErrors);
Vue.component('admin-delete', () => import('./admin/admin_delete/index'));
Vue.component('local-time', () => import('./common/local_time'));
Vue.component('password-field', passwordField);

// run gql query to see if the user is logged in, set state to ready
// and then initialize
apolloProvider.defaultClient.query({ query: MeQuery })
    .then((result) =>  {
        // don't set a user if we didn't get anything
        if (result.data.Me) {
            store.dispatch('updateUser', result.data.Me);
        }

        store.commit('ready');

        window.App = new Vue({
            el: '#app',
            router,
            store,
            apolloProvider,

            render: h => h(app),
        });
    });
