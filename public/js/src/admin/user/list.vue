<template>
    <div>
        <Portal to="header-actions">
            <RouterLink :to="{ name: 'admin-user-add' }"
                        class="header-action header-action-main">Add User</RouterLink>
            <div class="header-secondary_actions">
                <button type="button" class="button-link" @click="refresh">Refresh</button>
            </div>
        </Portal>

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
                <div class="record_list-record_count">Showing {{ users.length }} or {{ userCount }}</div>

                <ul class="record_list-wrap">
                    <li class="record_list-headers">
                        <div class="record_list-col">Username</div>
                        <div class="record_list-col">Name</div>
                        <div class="record_list-col">Account Status</div>
                        <div class="record_list-col">Last Login (Count)</div>
                        <div class="record_list-col">Role</div>
                        <div class="record_list-col"></div>
                    </li>

                    <li v-for="user in users"
                        :key="user.userId"
                        :class="{ 'record_list-item-inactive' : (!user.active || !user.verified) }"
                        class="record_list-item">
                        <div class="record_list-col">
                            {{ user.email }}
                            <span v-if="user.userId === rootStore.user.userId" class="pl-3 italic">
                                You
                            </span>
                        </div>
                        <div class="record_list-col">
                            <RouterLink :to="{ name: 'admin-user-view', params: { userId: user.userId } }">
                                {{ user.name }}
                            </RouterLink>
                        </div>
                        <div class="record_list-col"><AccountStatus :user="user" /></div>
                        <div class="record_list-col user_list-last_login">
                            <template v-if="user.loginCount > 0">
                                <LocalTime v-if="user.lastLogin" :datetime="user.lastLogin" />
                                ({{ user.loginCount }})
                            </template>
                            <i v-else>Never logged in</i>
                        </div>
                        <div class="record_list-col">{{ rootStore.availableRoles[user.roles[0]] }}</div>

                        <div class="record_list-col record_list-col-actions">
                            <RouterLink :to="{ name: 'admin-user-edit', params: { userId: user.userId } }">
                                Edit
                            </RouterLink>
                        </div>
                    </li>
                </ul>

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
import AccountStatus from './component/account_status.vue';

const rootStore = useRootStore();
const filtersStore = useFiltersStore();
const route = useRoute();
const router = useRouter();

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
});
onResult(({ data: { Users }}) => {
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
}

const refresh = () => {
    sendEvent({ type: 'REFRESH' });
    usersRefetch();
    userCountRefetch();
}

function cleanQueryFilters (queryFilters) {
    if (queryFilters.active) {
        queryFilters.active = 'true' === queryFilters.active;
    } else {
        delete queryFilters.active;
    }
    if (!queryFilters.roles) {
        delete queryFilters.roles;
    }

    return queryFilters;
}

const resetFilters = () => {
    filters.value = {
        ...defaultFilters,
    };
    offset.value = 0;
}
</script>
