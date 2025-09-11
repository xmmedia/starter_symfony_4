<template>
    <div>
        <Teleport to="#header-page-title"><span>Users</span></Teleport>
        <Teleport to="#header-actions">
            <RouterLink :to="{ name: 'admin-user-add' }"
                        class="header-action header-action-main">Add User</RouterLink>
            <div class="header-secondary_actions">
                <button type="button" class="button-link" @click="refresh">Refresh</button>
            </div>
        </Teleport>

        <FilterForm v-model="filters" @reset="resetFilters" />

        <LoadingSpinner v-if="state.matches('loading')">
            Loading users…
        </LoadingSpinner>
        <div v-if="state.matches('error')" class="italic text-center">
            There was a problem loading the user list. Please try again later.
        </div>

        <template v-if="state.matches('loaded')">
            <div v-if="users.length === 0" class="italic text-center">
                No users were found.
            </div>

            <template v-else>
                <Pagination route-name="admin-user"
                            :count="userCount || 0"
                            :offset="+offset"
                            :route-query-additions="{ filters }"
                            class="my-2" />
                <div class="record_list-record_count">
                    Showing {{ users.length }} of {{ Number(userCount).toLocaleString() }}
                </div>
                <RecordList :headings="[ 'Username', 'Name', 'Account Status', 'Last Login (Count)', 'Role', '' ]"
                            :items="users"
                            :cell-classes="[null, null, null, null, null, 'record_list-col-actions']">
                    <template #col1="{ item }">
                        {{ item.email }}
                    </template>
                    <template #col2="{ item }">
                        <RouterLink :to="{ name: 'admin-user-view', params: { userId: item.userId } }">
                            {{ item.name }}
                        </RouterLink>
                    </template>
                    <template #col3="{ item }">
                        <AccountStatus :user="item" />
                    </template>
                    <template #col4="{ item }">
                        <template v-if="item.loginCount > 0">
                            <LocalTime v-if="item.lastLogin" :datetime="item.lastLogin" />
                            ({{ item.loginCount }})
                        </template>
                        <i v-else>Never logged in</i>

                    </template>
                    <template #col5="{ item }">
                        {{ rootStore.availableRoles[item.roles[0]] }}
                    </template>
                    <template #col6="{ item }">
                        <RouterLink :to="{ name: 'admin-user-edit', params: { userId: item.userId } }">
                            Edit
                        </RouterLink>
                    </template>
                </RecordList>

                <Pagination route-name="admin-user"
                            :count="userCount || 0"
                            :offset="+offset"
                            :route-query-additions="{ filters }"
                            class="my-4" />
            </template>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { useRootStore } from '@/admin/stores/root';
import { useFiltersStore } from '@/admin/stores/filters';
import { useQuery } from '@vue/apollo-composable';
import { GetUsersQuery, GetUserCountQuery } from '../queries/user.query.graphql';
import FilterForm from './component/filter_form.vue';
import { useRoute, useRouter } from 'vue-router';
import Pagination from '@/common/pagination.vue';
import RecordList from '@/common/record_list.vue';
import AccountStatus from './component/account_status.vue';

const router = useRouter();
const route = useRoute();
const rootStore = useRootStore();
const filtersStore = useFiltersStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'loading',
    states: {
        loading: {
            on: {
                LOADED: 'loaded',
                ERROR: 'error',
            },
        },
        loaded: {
            on: {
                REFRESH: 'loading',
                UPDATE: 'loading',
            },
        },
        error: {
            on: {
                REFRESH: 'loading',
                UPDATE: 'loading',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const users = ref();
const defaultFilters = {
    q: null,
    role: 'ALL',
    accountStatus: 'ALL',
};
const cleanQueryFilters = (queryFilters) => {
    if ('ALL' === queryFilters.accountStatus) {
        delete queryFilters.accountStatus;
    }
    if (!queryFilters.roles) {
        delete queryFilters.roles;
    }

    return queryFilters;
};
const filters = ref({
    ...defaultFilters,
    ...filtersStore.user,
    ...cleanQueryFilters(route.query.filters || {}),
});
const offset = ref(route.query.offset || 0);

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

    if (setFilters.offset < 1) {
        delete setFilters.offset;
    }

    return setFilters;
});
const gqlFilters = computed(() => {
    const filters = {
        ...appliedFilters.value.filters,
        offset: appliedFilters.value.offset,
    };

    switch (filters.role) {
        case 'ALL':
            filters.roles = null;
            break;
        case 'ADMIN':
            filters.roles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
            break;
        case 'USER':
            filters.roles = ['ROLE_USER'];
            break;
    }
    delete filters.role;

    return filters;
});

const { loading, onResult, onError, refetch: usersRefetch } = useQuery(GetUsersQuery, {
    filters: gqlFilters,
}, {
    debounce: 500,
});
onResult(({ data: { Users }}) => {
    if (!Users) {
        sendEvent({ type: 'ERROR' });
        return;
    }

    users.value = Users;
    sendEvent({ type: 'LOADED' });
});
onError(() => {
    sendEvent({ type: 'ERROR' });
});

const { result: userCountResult, refetch: userCountRefetch } = useQuery(GetUserCountQuery, {
    filters: gqlFilters,
});
const userCount = computed(() => userCountResult.value?.UserCount);

onMounted(() => {
    updateQuery();
    filtersStore.setUser(filters.value);
});

watch(loading, (loading) => {
    if (loading) {
        sendEvent({ type: 'UPDATE' });
    }
});
watch(route, (route) => {
    if (undefined !== route.query.offset) {
        offset.value = +route.query.offset;
    }
});
watch(filters, () => {
    offset.value = 0;
    filtersStore.setUser(filters.value);
}, { deep: true });
watch(appliedFilters, () => {
    updateQuery();
}, { deep: true });

const updateQuery = () => {
    router.push({
        name: 'admin-user',
        query: { ...appliedFilters.value },
    });
};

const refresh = () => {
    sendEvent({ type: 'REFRESH' });
    usersRefetch();
    userCountRefetch();
};

const resetFilters = () => {
    filters.value = {
        ...defaultFilters,
    };
    offset.value = 0;
};
</script>
