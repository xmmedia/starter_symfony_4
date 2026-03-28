<template>
    <div>
        <Teleport to="#header-page-title"><span>Messenger Queue</span></Teleport>
        <Teleport to="#header-actions">
            <div class="header-secondary_actions">
                <button type="button" class="button-link" @click="refresh">Refresh</button>
            </div>
        </Teleport>

        <FilterForm v-model="filters" @reset="resetFilters" />

        <LoadingSpinner v-if="state.matches('loading')">
            Loading messenger queue…
        </LoadingSpinner>
        <div v-if="state.matches('error')" class="italic text-center">
            There was a problem loading the queue. Please try again later.
        </div>

        <template v-if="state.matches('loaded')">
            <div v-if="!messages.length" class="italic text-center">
                No records found.
            </div>
            <template v-else>
                <Pagination route-name="admin-messenger-queue"
                            :count="messageCount"
                            :offset="+offset"
                            :route-query-additions="{ filters }"
                            class="my-2" />

                <div class="record_list-record_count">
                    Showing {{ messages.length }} of {{ messageCount.toLocaleString() }}
                </div>
                <RecordList :headings="headings"
                            :items="messages"
                            :cell-classes="[null, null, null, null, null, null, 'record_list-col-actions']"
                            class="p-0">
                    <template #col1="{ item }">
                        {{ item.id }}
                    </template>
                    <template #col2="{ item }">
                        {{ item.queueName }}
                    </template>
                    <template #col3="{ item }">
                        <div class="font-mono text-sm break-all">{{ item.messageClass ?? '–' }}</div>
                    </template>
                    <template #col4="{ item }">
                        <LocalTime :datetime="item.createdAt" />
                    </template>
                    <template #col5="{ item }">
                        <LocalTime :datetime="item.availableAt" />
                    </template>
                    <template #col6="{ item }">
                        <LocalTime v-if="item.deliveredAt" :datetime="item.deliveredAt" />
                        <span v-else class="italic text-gray-400">–</span>
                    </template>
                    <template #col7="{ item }">
                        <RouterLink :to="{ name: 'admin-messenger-queue-view', params: { id: item.id } }">
                            View
                        </RouterLink>
                    </template>
                </RecordList>

                <Pagination route-name="admin-messenger-queue"
                            :count="messageCount"
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
import { useQuery } from '@vue/apollo-composable';
import { useRoute, useRouter } from 'vue-router';
import {
    GetMessengerQueueMessagesQuery,
    GetMessengerQueueMessageCountQuery,
} from '@/admin/queries/messenger_queue_message.query.graphql';
import { logError } from '@/common/lib';
import { useFiltersStore } from '@/admin/stores/filters';
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

const defaultFilters = {
    queueName: 'ALL',
};

const filters = ref({
    ...defaultFilters,
    ...filtersStore.messengerQueue,
    ...(route.query.filters || {}),
});
const offset = ref(+route.query.offset || 0);
const messages = ref([]);
const headings = ['ID', 'Queue', 'Message Class', 'Created At', 'Available At', 'Delivered At', ''];

const appliedFilters = computed(() => {
    const setFilters = {
        filters: { ...defaultFilters, ...filters.value },
        offset: +offset.value,
    };

    for (const filter in setFilters.filters) {
        if (null === setFilters.filters[filter] || defaultFilters[filter] === setFilters.filters[filter]) {
            delete setFilters.filters[filter];
        }
    }

    if (setFilters.offset < 1) {
        delete setFilters.offset;
    }

    return setFilters;
});

const gqlFilters = computed(() => {
    const f = { offset: offset.value };

    if (filters.value.queueName && 'ALL' !== filters.value.queueName) {
        f.queueName = filters.value.queueName;
    }

    return f;
});

const { loading, onResult, onError, refetch: messagesRefetch } = useQuery(
    GetMessengerQueueMessagesQuery,
    { filters: gqlFilters },
    { debounce: 500 },
);
onResult(({ data }) => {
    if (null != data?.MessengerQueueMessages) {
        messages.value = data.MessengerQueueMessages;
        sendEvent({ type: 'LOADED' });
    } else {
        sendEvent({ type: 'ERROR' });
    }
});
onError((error) => {
    logError(error);
    sendEvent({ type: 'ERROR' });
});

const { result: messageCountResult, refetch: messageCountRefetch } = useQuery(
    GetMessengerQueueMessageCountQuery,
    { filters: gqlFilters },
    { debounce: 300 },
);
const messageCount = computed(() => messageCountResult.value?.MessengerQueueMessageCount ?? 0);

onMounted(() => {
    updateQuery();
    filtersStore.setMessengerQueue(filters.value);
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
    filtersStore.setMessengerQueue(filters.value);
}, { deep: true });
watch(appliedFilters, () => {
    updateQuery();
}, { deep: true });

const updateQuery = () => {
    router.push({
        name:  'admin-messenger-queue',
        query: appliedFilters.value,
    });
};

const refresh = () => {
    sendEvent({ type: 'REFRESH' });
    messagesRefetch();
    messageCountRefetch();
};

const resetFilters = () => {
    filters.value = { ...defaultFilters };
    offset.value = 0;
};
</script>
