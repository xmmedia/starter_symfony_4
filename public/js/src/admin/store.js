import Vue from 'vue';
import Vuex from 'vuex';

import adminMenu from './menu/store';

Vue.use(Vuex);

export default new Vuex.Store({
    namespaced: true,
    strict: process.env.NODE_ENV !== 'production',

    state: {
        ready: false,
        user: null,

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

            return !!state.user;
        },

        hasRole: (state, getters) => (role) => {
            if (!state.user) {
                return false;
            }

            // all logged in users have ROLE_USER
            if (role === 'ROLE_USER') {
                return true;
            }

            if (state.user.roles === null) {
                return false;
            }

            return state.user.roles.includes(role);
        },
    },

    actions: {
        updateUser ({ commit }, user) {
            commit('setUser', user);
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
