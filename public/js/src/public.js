import 'babel-polyfill';

import Vue from 'vue';

import svgIcons from './common/svg_icons';

// SASS/CSS
import '../../css/sass/public.scss';

// images
// import '../../images/icons-public.svg';

// disable the warning about dev/prod
Vue.config.productionTip = false;

window.App = new Vue({
    el: '#app',
    components: {
        'svg-icons': svgIcons,
    }
});
