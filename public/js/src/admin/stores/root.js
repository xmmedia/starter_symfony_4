import { defineStore } from 'pinia';

export const useRootStore = defineStore('rootStore', {
    state: () => ({
        ready: false,
        user: null,
        availableRoles: {
            ROLE_USER: 'User',
            ROLE_ADMIN: 'Admin',
            ROLE_SUPER_ADMIN: 'Super Admin',
        },
        // entrypointIntegrityHashes: {
        //     admin: null,
        // },
    }),

    getters: {
        loggedIn: (state) => {
            if (!state.ready) {
                return false;
            }

            return !!state.user;
        },

        hasRole (state) {
            return (role) => {
                if (!this.loggedIn) {
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
            };
        },
    },

    actions: {
        ready () {
            this.ready = true;
        },
        updateUser (user) {
            if (this.user === null) {
                this.user = { ...user };
            } else {
                this.user = { ...this.user, ...user };
            }
        },

        /*setIntegrityHash ({ entrypoint, hash }) {
            if (this.entrypointIntegrityHashes[entrypoint]) {
                // eslint-disable-next-line no-console
                console.error('Integrity hash already set for '+entrypoint+' entry point. Won\'t update.');
                return;
            }

            this.entrypointIntegrityHashes[entrypoint] = hash
        },*/
    },
});
