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
        // @todo-symfony
        pageTitleSuffix: 'XM Media Inc.',

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

            return null !== state.user;
        },

        hasRole: (state, getters) => (role) => {
            if (!getters.loggedIn) {
                return false;
            }

            // all logged in users have ROLE_USER
            if (role === 'ROLE_USER') {
                return true;
            }

            for (let r = 0; r < state.user.roles.length; r++) {
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
                let visited = [];

                roles.forEach((role) => {
                    if (!map.hasOwnProperty(role)) {
                        return;
                    }

                    visited.push(role);

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
