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

        // also in Symfony security config
        roleHierarchy: {
            ROLE_ADMIN: ['ROLE_USER'],
            ROLE_SUPER_ADMIN: ['ROLE_ADMIN'],
        },
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

            for (let r = 0; r < state.user.roles.length; r++) {
                // ROLE_USER is checked above, so skip
                if (state.user.roles[r] === 'ROLE_USER') {
                    continue;
                }

                if (getters.roleMap[state.user.roles[r]].has(role)) {
                    return true;
                }
            }

            return false;
        },

        roleMap (state) {
            const map = { ...state.roleHierarchy };

            for (let main in state.roleHierarchy) {
                const roles = state.roleHierarchy[main];
                map[main] = new Set([...roles]);
                map[main].add(main);

                roles.forEach((role) => {
                    if (!Object.prototype.hasOwnProperty.call(map, role)) {
                        return;
                    }

                    state.roleHierarchy[role].forEach((roleToAdd) => {
                        map[main].add(roleToAdd);
                    });
                });
            }

            return map;
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
