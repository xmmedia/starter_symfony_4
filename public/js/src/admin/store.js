import Vue from 'vue';
import Vuex from 'vuex';

import adminMenu from './menu/store';

Vue.use(Vuex);

export default new Vuex.Store({
    namespaced: true,
    strict: true,
    state: {
        serverData: {},
    },
    getters: {},
    actions: {},
    mutations: {
        updateServerData (state, serverData) {
            state.serverData = serverData;
        },
    },

    modules: {
        adminMenu,
    }
});