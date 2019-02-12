import Vue from 'vue';
import VueRouter from 'vue-router';
import VueApollo from 'vue-apollo';
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

Vue.use(VueRouter);
Vue.use(VueApollo);
Vue.use(PortalVue);
Vue.use(VueModal);

// global components
Vue.component('field-errors', fieldErrors);
Vue.component('admin-delete', () => import('./admin/admin_delete/index'));
Vue.component('local-time', () => import('./common/local_time'));
Vue.component('password-field', passwordField);

window.App = new Vue({
    el: '#app',
    router,
    store,
    apolloProvider,

    components: {
        app,
    },

    apollo: {
        user: {
            query: MeQuery,
            update (data) {
                // don't set a user if we didn't get anything
                if (data.Me) {
                    this.$store.dispatch(
                        'updateUser',
                        data.Me
                    );
                }
                this.$store.commit('ready');
            },
        },
    },

    render: function(h) {
        return h(app);
    },
});
