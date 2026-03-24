<template>
    <div>
        <Teleport to="#header-page-title"><span>Authentication Log</span></Teleport>
        <Teleport to="#header-actions">
            <div class="header-secondary_actions">
                <button type="button" class="button-link" @click="refresh">Refresh</button>
            </div>
        </Teleport>

        <FilterForm v-model="filters" @reset="resetFilters" />

        <LoadingSpinner v-if="state.matches('loading')">
            Loading authentication log…
        </LoadingSpinner>
        <div v-if="state.matches('error')" class="italic text-center">
            There was a problem loading the authentication log. Please try again later.
        </div>

        <template v-if="state.matches('loaded')">
            <div v-if="!authLogs.length" class="italic text-center">
                No records found.
            </div>
            <template v-else>
                <Pagination route-name="admin-auth-log"
                            :count="authLogsCount"
                            :offset="+offset"
                            :route-query-additions="{ filters }"
                            class="my-2" />

                <div class="record_list-record_count">
                    Showing {{ authLogs.length }} of {{ authLogsCount.toLocaleString() }}
                </div>
                <RecordList :headings="headings"
                            :items="authLogs"
                            :cell-classes="[null, null, null, null, null, null, 'record_list-col-actions']"
                            class="p-0">
                    <template #col1="{ item }">
                        <LocalTime :datetime="item.occurredAt" />
                    </template>
                    <template #col2="{ item }">
                        <EventTypeLabel :event-type="item.eventType" />
                    </template>
                    <template #col3="{ item }">
                        <template v-if="item.user">
                            <router-link v-if="'LOGIN' === item.eventType"
                                         :to="{ name: 'admin-user-view', params: { userId: item.user.userId } }">
                                {{ item.user.email }}
                            </router-link>
                            <span v-else>{{ item.user.email }}</span>
                        </template>
                        <span v-else-if="item.email">{{ item.email }}</span>
                        <template v-if="item.impersonatedUser">
                            <div class="text-sm text-gray-500">
                                →
                                <router-link :to="{
                                    name: 'admin-user-view',
                                    params: { userId: item.impersonatedUser.userId },
                                }">{{ item.impersonatedUser.email }}</router-link>
                            </div>
                        </template>
                    </template>
                    <template #col4="{ item }">
                        {{ item.ipAddress }}
                    </template>
                    <template #col5="{ item }">
                        {{ formatUserAgent(item.userAgent) }}
                    </template>
                    <template #col6="{ item }">
                        {{ item.errorMessage ?? '–' }}
                    </template>
                    <template #col7="{ item }">
                        <RouterLink :to="{ name: 'admin-auth-log-view', params: { authLogId: item.authLogId } }">
                            View
                        </RouterLink>
                    </template>
                </RecordList>

                <Pagination route-name="admin-auth-log"
                            :count="authLogsCount"
                            :offset="+offset"
                            :route-query-additions="{ filters }"
                            class="my-4" />
            </template>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { UAParser } from 'ua-parser-js';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { useQuery } from '@vue/apollo-composable';
import { useRoute, useRouter } from 'vue-router';
import { GetAuthLogsQuery, GetAuthLogsCountQuery } from '@/admin/queries/auth_log.query.graphql';
import { logError } from '@/common/lib';
import { useFiltersStore } from '@/admin/stores/filters';
import EventTypeLabel from './component/event_type_label.vue';
import FilterForm from './component/filter_form.vue';
import RecordList from '@/common/record_list.vue';
import Pagination from '@/common/pagination.vue';

const router = useRouter();
const route = useRoute();
const filtersStore = useFiltersStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'loading',
    states: {
        loading: {
            on: {
                LOADED: 'loaded',
                ERROR:  'error',
            },
        },
        loaded: {
            on: {
                REFRESH: 'loading',
                UPDATE:  'loading',
            },
        },
        error: {
            on: {
                REFRESH: 'loading',
                UPDATE:  'loading',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const formatUserAgent = (ua) => {
    if (!ua) {
        return '–';
    }

    const { browser, os } = UAParser(ua);
    const parts = [];

    if (browser.name) {
        parts.push(browser.version ? `${browser.name} ${browser.version.split('.')[0]}` : browser.name);
    }

    if (os.name) {
        parts.push(os.version ? `${os.name} ${os.version}` : os.name);
    }

    return parts.length ? parts.join(' / ') : ua;
};

const defaultFilters = {
    eventType:      'ALL',
    dateRange:      'LAST_24H',
    customDateFrom: null,
    customDateTo:   null,
    q:              null,
};

const cleanQueryFilters = (queryFilters) => {
    if (!queryFilters.eventType) {
        delete queryFilters.eventType;
    }

    return queryFilters;
};

const filters = ref({
    ...defaultFilters,
    ...filtersStore.authLog,
    ...cleanQueryFilters(route.query.filters || {}),
});
const offset = ref(+route.query.offset || 0);
const authLogs = ref([]);
const headings = ['Date/Time', 'Event', 'User', 'IP Address', 'User Agent', 'Error', ''];

const appliedFilters = computed(() => {
    const setFilters = {
        filters: { ...defaultFilters, ...filters.value },
        offset: +offset.value,
    };

    for (const filter in setFilters.filters) {
        if (null === setFilters.filters[filter] || defaultFilters[filter] === setFilters.filters[filter]) {
            delete setFilters.filters[filter];

            continue;
        }

        switch (filter) {
            case 'q':
                if ('' === setFilters.filters.q) {
                    delete setFilters.filters.q;
                }
                break;
        }
    }

    if ('CUSTOM' !== setFilters.filters.dateRange) {
        delete setFilters.filters.customDateFrom;
        delete setFilters.filters.customDateTo;
    }

    if (setFilters.offset < 1) {
        delete setFilters.offset;
    }

    return setFilters;
});
const gqlFilters = computed(() => {
    let eventTypes = null;

    switch (filters.value.eventType) {
        case 'login':
            eventTypes = ['LOGIN'];
            break;
        case 'login_failed':
            eventTypes = ['LOGIN_FAILED'];
            break;
        case 'impersonation':
            eventTypes = ['IMPERSONATION_STARTED', 'IMPERSONATION_ENDED'];
            break;
    }

    const toISO = (d) => d.toISOString().slice(0, 19);
    let dateFrom = null;
    let dateTo = null;

    switch (filters.value.dateRange) {
        case 'LAST_HOUR':
            dateFrom = toISO(new Date(Date.now() - 60 * 60 * 1000));
            break;
        case 'LAST_24H':
            dateFrom = toISO(new Date(Date.now() - 24 * 60 * 60 * 1000));
            break;
        case 'LAST_WEEK':
            dateFrom = toISO(new Date(Date.now() - 7 * 24 * 60 * 60 * 1000));
            break;
        case 'LAST_MONTH':
            dateFrom = toISO(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000));
            break;
        case 'CUSTOM':
            if (filters.value.customDateFrom && filters.value.customDateTo) {
                dateFrom = new Date(filters.value.customDateFrom + 'T00:00:00').toISOString().slice(0, 19);
                dateTo = new Date(filters.value.customDateTo + 'T23:59:59').toISOString().slice(0, 19);
            }
            break;
    }

    return {
        eventTypes,
        dateFrom,
        dateTo,
        q:      filters.value.q || null,
        offset: offset.value,
    };
});

const { loading, onResult, onError, refetch: authLogsRefetch } = useQuery(
    GetAuthLogsQuery,
    { filters: gqlFilters },
    { debounce: 500 },
);
onResult(({ data }) => {
    if (null != data?.AuthLogs) {
        authLogs.value = data.AuthLogs;
        sendEvent({ type: 'LOADED' });
    } else {
        sendEvent({ type: 'ERROR' });
    }
});
onError((error) => {
    logError(error);
    sendEvent({ type: 'ERROR' });
});

const { result: authLogsCountResult, refetch: authLogsCountRefetch } = useQuery(
    GetAuthLogsCountQuery,
    { filters: gqlFilters },
    { debounce: 300 },
);
const authLogsCount = computed(() => authLogsCountResult.value?.AuthLogsCount);

onMounted(() => {
    updateQuery();
    filtersStore.setAuthLog(filters.value);
});

watch(loading, (isLoading) => {
    if (isLoading) {
        sendEvent({ type: 'UPDATE' });
    }
});
watch(route, (newRoute) => {
    if (undefined !== newRoute.query.offset) {
        offset.value = +newRoute.query.offset;
    }
});
watch(filters, () => {
    offset.value = 0;
    filtersStore.setAuthLog(filters.value);
}, { deep: true });
watch(appliedFilters, () => {
    updateQuery();
}, { deep: true });

const updateQuery = () => {
    router.push({
        name:  'admin-auth-log',
        query: appliedFilters.value,
    });
};

const refresh = () => {
    sendEvent({ type: 'REFRESH' });
    authLogsRefetch();
    authLogsCountRefetch();
};

const resetFilters = () => {
    filters.value = { ...defaultFilters };
    offset.value = 0;
};
</script>
