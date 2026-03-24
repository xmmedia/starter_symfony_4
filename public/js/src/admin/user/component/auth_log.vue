<template>
    <div class="record_view-item_wrap mt-16 max-w-6xl">
        <div class="record_view-header_wrap">
            <h3 class="m-0">Authentication History</h3>
        </div>
        <LoadingSpinner v-if="state.matches('loading')">
            Loading authentication history…
        </LoadingSpinner>
        <div v-else-if="state.matches('error')" class="italic text-center">
            There was a problem loading the authentication history. Please try again later.
        </div>
        <template v-else-if="state.matches('loaded')">
            <div v-if="!authLogs.length" class="italic text-center">
                No history available.
            </div>
            <template v-else>
                <Pagination class="my-2"
                            :count="authLogsCount"
                            :offset="offset"
                            @update:offset="offset = $event" />
                <div class="record_list-record_count">
                    Showing {{ authLogs.length }} of {{ authLogsCount.toLocaleString() }}
                </div>
                <RecordList :headings="headings" :items="authLogs" class="p-0">
                    <template #col1="{ item }">
                        <LocalTime :datetime="item.occurredAt" />
                    </template>
                    <template #col2="{ item }">
                        <div>
                            <template v-if="'IMPERSONATION_STARTED' === item.eventType">
                                {{ item.impersonatedUser?.userId === props.userId
                                    ? 'Impersonation Started'
                                    : 'Started Impersonating' }}
                            </template>
                            <EventTypeLabel v-else :event-type="item.eventType" />
                        </div>
                        <div v-if="item.impersonatedUser" class="text-sm text-gray-500">
                            <template v-if="item.impersonatedUser.userId === props.userId">
                                By <router-link :to="{ name: 'admin-user-view', params: { userId: item.user.userId } }">
                                    {{ item.user.email }}
                                </router-link>
                            </template>
                            <router-link v-else
                                         :to="{ name: 'admin-user-view',
                                                params: { userId: item.impersonatedUser.userId } }">
                                {{ item.impersonatedUser.email }}
                            </router-link>
                        </div>
                        <div v-else-if="'LOGIN' !== item.eventType && item.email" class="text-sm text-gray-500">
                            {{ item.email }}
                        </div>
                    </template>
                    <template #col3="{ item }">
                        {{ item.ipAddress }}
                    </template>
                    <template #col4="{ item }">
                        {{ formatUserAgent(item.userAgent) }}
                    </template>
                    <template #col5="{ item }">
                        {{ item.errorMessage ?? '–' }}
                    </template>
                </RecordList>
                <Pagination class="my-4"
                            :count="authLogsCount"
                            :offset="offset"
                            @update:offset="offset = $event" />
            </template>
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { UAParser } from 'ua-parser-js';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { view as stateMachineConfig } from '@/common/state_machines';
import { useQuery } from '@vue/apollo-composable';
import { GetUserAuthLogsQuery } from '@/admin/queries/user.query.graphql';
import { logError } from '@/common/lib';
import EventTypeLabel from '@/admin/auth_log/component/event_type_label.vue';
import RecordList from '@/common/record_list.vue';
import Pagination from '@/common/pagination.vue';

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

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
});

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const authLogs = ref([]);
const authLogsCount = ref(0);
const offset = ref(0);
const headings = ['Date/Time', 'Event', 'IP Address', 'User Agent', 'Error'];

const { onResult, onError } = useQuery(
    GetUserAuthLogsQuery,
    () => ({ userId: props.userId, offset: offset.value, limit: 30 }),
);

onResult(({ data }) => {
    if (data?.User) {
        authLogs.value = data.User.authLogs;
        authLogsCount.value = data.User.authLogsCount;
        sendEvent({ type: 'LOADED' });
    } else {
        sendEvent({ type: 'ERROR' });
    }
});

onError((error) => {
    logError(error);
    sendEvent({ type: 'ERROR' });
});
</script>
