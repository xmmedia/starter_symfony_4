import { createApp, h, defineAsyncComponent } from 'vue';
// @todo-symfony if need portals, uncomment here and below
// import PortalVue from 'portal-vue';

// @todo-symfony if using graphql on the public portion of the website, uncomment throughout file
// import apolloProvider from './common/apollo';

import menuMain from './public/menu_main/index';
// @todo-symfony remove if not using the form components on the public portion of the website
import formError from './common/form_error';
import fieldError from './common/field_error';

// SASS/CSS
import '../../css/public.scss';

// images
import '@/../../images/icons-public.svg';

// @todo-symfony add the following below if needed for the public portion of the site
// .use(apolloProvider);
// .use(PortalVue);

createApp({
    render () {
        return h(menuMain);
    },
}).mount('#menu-main');

createApp({
    render () {
        return h(defineAsyncComponent(() => import('./public/contact/index')));
    },
})
    .component('form-error', formError)
    .component('field-error', fieldError)
    .mount('#form-contact');
