# Code Templates

For HTML, check [pattern_library_public.html.twig](https://github.com/xmmedia/starter_symfony_4/blob/master/templates/default/pattern_library_public.html.twig) & [pattern_library_admin.html.twig](https://github.com/xmmedia/starter_symfony_4/blob/master/templates/default/pattern_library_admin.html.twig)

## Vue

### Vue Component
```vue
<template>

</template>

<script>
export default {
    components: {},
    
    props: {},

    data () {
        return {};
    },

    computed: {},

    watch: {},

    beforeMount () {},

    mounted () {},

    methods: {},
}
</script>
```

### Vue Store
```javascript
const state = {
};

const getters = {
    // getter (state, getters) {
    //     return state.param;
    // },
    // getter: (state) => (value) => {
    //     return state.param[value];
    // },
};

const actions = {
    // action ({ commit, state, dispatch, rootState, rootGetters }, data) {
    //     commit('mutation');
    // },
};

const mutations = {
    // mutation (state, param) {
    //     state.param = param;
    // },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
```

### Vue Router
```javascript
import VueRouter from 'vue-router';

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            name: null,
            path: null,
            component: null,
        },
    ],

    scrollBehavior (to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        } else {
            return { x: 0, y: 0 }
        }
    },
});

export default router;
```
