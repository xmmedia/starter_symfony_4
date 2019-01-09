import Vue from 'vue';
import VueRouter from 'vue-router';
import VueApollo from 'vue-apollo';
import PortalVue from 'portal-vue';
import VueModal from 'vue-js-modal';

import router from './admin/router';
import store from './admin/store';
import apolloProvider from './common/apollo';

import svgIcons from './common/svg_icons';
import fieldErrors from './common/field_errors';
import menuSubnav from './admin/menu/subnav';
import menuSmall from './admin/menu/small';
import listCheck from './admin/list_check';
import loginForm from './admin/login/index';
import passwordField from './common/password_field';

// SASS/CSS
import '../../css/sass/admin.scss';

// images
import '../../images/icons-admin.svg';

// disable the warning about dev/prod
Vue.config.productionTip = false;

Vue.use(VueRouter);
Vue.use(VueApollo);
Vue.use(PortalVue);
Vue.use(VueModal);

// global components
Vue.component('field-errors', fieldErrors);
Vue.component('admin-delete', () => import('./admin/admin_delete'));
Vue.component('list-check', listCheck);
Vue.component('local-time', () => import('./common/local_time'));
Vue.component('password-field', passwordField);

window.App = new Vue({
    el: '#app',
    router,
    store,
    apolloProvider,

    components: {
        'svg-icons': svgIcons,
        'menu-subnav': menuSubnav,
        'menu-small': menuSmall,
        'login-form': loginForm,
        'admin-user': () => import('./admin/user/index'),
        'user-profile': () => import('./admin/user_profile_edit/index'),
    },

    mounted () {
        if (this.$el.dataset && this.$el.dataset.serverData) {
            this.$store.commit(
                'updateServerData',
                JSON.parse(this.$el.dataset.serverData)
            );
        }
    },
});
