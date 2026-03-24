import { acceptHMRUpdate, defineStore } from 'pinia';

export const useFiltersStore = defineStore('filtersStore', {
    state: () => ({
        user: {
            q: null,
            role: 'ALL',
            accountStatus: 'ALL',
        },
        authLog: {
            eventType:      'ALL',
            dateRange:      'LAST_24H',
            customDateFrom: null,
            customDateTo:   null,
            q:              null,
        },
    }),

    actions: {
        setUser (filters) {
            this.user = { ...filters };
        },
        setAuthLog (filters) {
            this.authLog = { ...filters };
        },
    },
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useFiltersStore, import.meta.hot))
}
