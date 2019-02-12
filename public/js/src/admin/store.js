import Vue from 'vue';
import Vuex from 'vuex';

import adminMenu from './menu/store';

Vue.use(Vuex);

export default new Vuex.Store({
    namespaced: true,
    strict: true,

    state: {
        ready: false,
        user: null,
        // @todo-symfony
        pageTitleSuffix: 'XM Media Inc.',

        availableRoles: {
            ROLE_USER: 'User',
            ROLE_ADMIN: 'Admin',
            ROLE_SUPER_ADMIN: 'Super Admin',
        },
    },

    getters: {
        loggedIn (state) {
            if (!state.ready) {
                return false;
            }

            return null !== state.user;
        },
    },

    actions: {
        updateUser ({ commit }, user) {
            commit('setUser', user);
        },

        updatePageTitle ({ state }, pageTitle) {
            if (pageTitle) {
                document.title = pageTitle + ' | ' + state.pageTitleSuffix;
            } else {
                document.title = state.pageTitleSuffix;
            }
        },
    },

    mutations: {
        ready (state) {
            state.ready = true;
        },
        setUser (state, user) {
            if (state.user === null) {
                Vue.set(state, 'user', { ...user });
            } else {
                Vue.set(state, 'user', { ...state.user, ...user });
            }
        },
    },

    modules: {
        adminMenu,
    },
});
