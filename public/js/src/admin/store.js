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
    actions: {
        updateUser ({ commit }, user) {
            commit('setUser', user);
        },
    },
    mutations: {
        updateServerData (state, serverData) {
            state.serverData = serverData;
        },
        setUser (state, user) {
            state.serverData.user = { ...state.serverData.user, ...user };
        },
    },

    modules: {
        adminMenu,
    },
});
