import { acceptHMRUpdate, defineStore } from 'pinia';

export const useFiltersStore = defineStore('filtersStore', {
    state: () => ({
        user: {
            q: null,
            role: 'ALL',
            accountStatus: 'ALL',
        },
    }),

    actions: {
        setUser (filters) {
            this.user = { ...filters };
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useFiltersStore, import.meta.hot))
}
