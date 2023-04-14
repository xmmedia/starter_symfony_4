import { createApp, defineAsyncComponent } from 'vue';
// @todo-symfony if need portals, uncomment here and below
// import PortalVue from 'portal-vue';

// @todo-symfony if using graphql on the public portion of the website, uncomment throughout file
// import apolloProvider from './common/apollo';

// import formError from './common/form_error';
// import fieldError from './common/field_error';

// SASS/CSS
import '../../css/public.scss';

// images
import '@/../../images/icons-public.svg';


window.App = createApp({
    components: {
        'contact-form': defineAsyncComponent(() => import('./public/contact/index')),
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

// window.App.use(apolloProvider);

// window.App.use(PortalVue);
// window.App.use(PortalVue);

// window.App.component('form-error', formError);
// window.App.component('field-error', fieldError);

window.App.mount('#app');
