import { acceptHMRUpdate, defineStore } from 'pinia';
import { formatPhone } from '@/common/lib';

export const useRootStore = defineStore('rootStore', {
    state: () => ({
        ready: false,
        user: null,
        entrypointIntegrityHashes: {
            user: null,
        },
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
            if (user.phoneNumber) {
                user.phoneNumber = formatPhone(user.phoneNumber);
            }

            if (this.user === null) {
                this.user = { ...user };
            } else {
                this.user = { ...this.user, ...user };
            }
        },

        setIntegrityHash ({ entrypoint, hash }) {
            if (this.entrypointIntegrityHashes[entrypoint]) {
                // eslint-disable-next-line no-console
                console.error('Integrity hash already set for '+entrypoint+' entry point. Won\'t update.');
                return;
            }

            this.entrypointIntegrityHashes[entrypoint] = hash
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useRootStore, import.meta.hot))
}
