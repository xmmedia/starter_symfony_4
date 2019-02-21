import Vue from 'vue';
import VueApollo from 'vue-apollo';
import PortalVue from 'portal-vue';

import apolloProvider from './common/apollo';

import svgIcons from './common/svg_icons';
import fieldErrors from './common/field_errors';

// SASS/CSS
import '../../css/sass/public.scss';

// images
// import '../../images/icons-public.svg';

// disable the warning about dev/prod
Vue.config.productionTip = false;

Vue.use(VueApollo);
Vue.use(PortalVue);

Vue.component('field-errors', fieldErrors);

window.App = new Vue({
    el: '#app',
    apolloProvider,

    components: {
        'svg-icons': svgIcons,
        'contact-form': () => import(/* webpackChunkName: "public-contact" */ './public/contact/index'),
    },
});
