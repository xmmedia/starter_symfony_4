import 'es6-promise/auto';

import Vue from 'vue';
import PortalVue from 'portal-vue';
import VueModal from 'vue-js-modal';
import VueMeta from 'vue-meta';
import Vuelidate from 'vuelidate';
import AsyncComputed from 'vue-async-computed';

import router from './admin/router';
import store from './admin/store';
import apolloProvider from './common/apollo';

import app from './admin/index';

import loadingSpinner from './common/loading_spinner';
import formError from './common/form_error';
import fieldErrors from './common/field_errors';
import fieldError from './common/field_error';
import fieldPassword from './common/field_password';
import adminButton from './common/admin_button';
import { MeQuery } from './admin/queries/user.query.graphql';

import * as filters from './common/filters';

// SASS/CSS
import '../../css/admin.scss';

// disable the warning about dev/prod
Vue.config.productionTip = false;

Vue.use(PortalVue);
Vue.use(VueModal, { componentName: 'vue-modal' });
Vue.use(VueMeta);
Vue.use(Vuelidate);
Vue.use(AsyncComputed);

// global components
Vue.component('loading-spinner', loadingSpinner);
Vue.component('form-error', formError);
Vue.component('field-errors', fieldErrors);
Vue.component('field-error', fieldError);
Vue.component('field-password', fieldPassword);
Vue.component('admin-button', adminButton);
Vue.component('admin-modal', () => import(/* webpackChunkName: "admin-modal" */ './common/modal'));
Vue.component('admin-delete', () => import(/* webpackChunkName: "admin-delete" */ './admin/admin_delete/index'));
Vue.component('local-time', () => import(/* webpackChunkName: "local-time" */ './common/local_time'));

Vue.filter('formatPhone', filters.formatPhone);
Vue.filter('date', filters.date);
Vue.filter('money', filters.money);
Vue.filter('pluralize', filters.pluralize);

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

            metaInfo: {
                // @todo-symfony
                title: 'Symfony Starter',
            },
        });
    });
