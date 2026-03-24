import { acceptHMRUpdate, defineStore } from 'pinia';

export const useFiltersStore = defineStore('filtersStore', {
    state: () => ({
        authLog: {
            eventType:      'ALL',
            dateRange:      'LAST_24H',
            customDateFrom: null,
            customDateTo:   null,
            q:              null,
        },
        user: {
            q: null,
            role: 'ALL',
            accountStatus: 'ALL',
        },
    }),

    actions: {
        setAuthLog (filters) {
            this.authLog = { ...filters };
        },
        setUser (filters) {
            this.user = { ...filters };
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useFiltersStore, import.meta.hot))
}
