import { defineStore } from 'pinia';

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
