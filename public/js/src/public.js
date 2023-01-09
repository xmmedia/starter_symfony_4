import Vue from 'vue';
// @todo-symfony if using graphql on the public portion of the website, uncomment throughout file
// import VueApollo from 'vue-apollo';
import Vuelidate from 'vuelidate';
// @todo-symfony if need portals, uncomment here and below
// import PortalVue from 'portal-vue';

// import apolloProvider from './common/apollo';
// import * as filters from './common/filters';

import formError from './common/form_error';
import fieldError from './common/field_error';

// SASS/CSS
import '../../css/public.scss';

// images
import '@/../../images/icons-public.svg';

// disable the warning about dev/prod
Vue.config.productionTip = false;

// Vue.use(VueApollo);
Vue.use(Vuelidate);
// Vue.use(PortalVue);

// Vue.component('form-error', formError);
// Vue.component('field-error', fieldError);

window.App = new Vue({
    el: '#app',
    // apolloProvider,

    components: {
        'contact-form': () => import(/* webpackChunkName: "public-contact" */ './public/contact/index'),
    },

    data () {
        return {
            showMobileMenu: false,
        };
    },

    mounted () {
        this.$nextTick(() => {
            window.addEventListener('resize', () => { this.showMobileMenu = false });
        });
    },

    methods: {
        toggleMobileMenu () {
            this.showMobileMenu = !this.showMobileMenu;
        },
    },
});
