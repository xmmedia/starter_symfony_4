import Vue from 'vue';
import PortalVue from 'portal-vue';
import VueModal from 'vue-js-modal';

import store from './admin/store';

import svgIcons from './common/svg_icons';
import fieldErrors from './common/field_errors';
import menuSubnav from './admin/menu/subnav';
import menuSmall from './admin/menu/small';
import listCheck from './admin/list_check';

// SASS/CSS
import '../../css/sass/admin.scss';

// images
import '../../images/icons-admin.svg';

// disable the warning about dev/prod
Vue.config.productionTip = false;

Vue.use(PortalVue);
Vue.use(VueModal);

// global components
Vue.component('field-errors', fieldErrors);
Vue.component('admin-delete', () => import('./admin/admin_delete'));
Vue.component('list-check', listCheck);
Vue.component('local-time', () => import('./common/local_time'));

window.App = new Vue({
    el: '#app',
    store,

    components: {
        'svg-icons': svgIcons,
        'menu-subnav': menuSubnav,
        'menu-small': menuSmall,
        'admin-user': () => import('./admin/user/form'),
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
