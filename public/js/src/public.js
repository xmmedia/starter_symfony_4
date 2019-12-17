import 'es6-promise/auto';

import Vue from 'vue';
import VueApollo from 'vue-apollo';
import PortalVue from 'portal-vue';

import apolloProvider from './common/apollo';
// import * as filters from './common/filters';

import loadingSpinner from './common/loading_spinner';
import svgIcons from './common/svg_icons';
import formError from './common/form_error';
import fieldErrors from './common/field_errors';
import fieldError from './common/field_error';

// SASS/CSS
import '../../css/public.scss';

// images
// import '../../images/icons-public.svg';

// disable the warning about dev/prod
Vue.config.productionTip = false;

Vue.use(VueApollo);
Vue.use(PortalVue);

Vue.component('loading-spinner', loadingSpinner);
Vue.component('form-error', formError);
Vue.component('field-errors', fieldErrors);
Vue.component('field-error', fieldError);
Vue.component('local-time', () => import(/* webpackChunkName: "local-time" */ './common/local_time'));

window.App = new Vue({
    el: '#app',
    apolloProvider,

    components: {
        svgIcons,
        'contact-form': () => import(/* webpackChunkName: "public-contact" */ './public/contact/index'),
    },
});
