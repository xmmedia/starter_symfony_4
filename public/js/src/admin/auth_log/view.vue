<template>
    <Teleport to="#header-actions">
        <div class="header-secondary_actions">
            <RouterLink :to="{ name: 'admin-auth-log' }">Return to list</RouterLink>
        </div>
    </Teleport>

    <LoadingSpinner v-if="state.matches('loading')">
        Loading…
    </LoadingSpinner>
    <div v-else-if="state.matches('not_found')" class="italic text-center">
        <p>The record could not be found.</p>
        <p><RouterLink :to="{ name: 'admin-auth-log' }">Return to list</RouterLink></p>
    </div>
    <div v-else-if="state.matches('error')" class="italic text-center">
        <p>There was a problem loading this record. Please try again later.</p>
        <p><RouterLink :to="{ name: 'admin-auth-log' }">Return to list</RouterLink></p>
    </div>

    <template v-if="state.matches('loaded')">
        <Teleport to="#header-page-title">
            <span><EventTypeLabel :event-type="authLog.eventType" /></span>
        </Teleport>

        <div class="record_view-item_wrap">
            <div class="record_view-item">
                <div class="record_view-item_label">Date/Time</div>
                <div class="record_view-item_value">
                    <LocalTime :datetime="authLog.occurredAt" />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Event</div>
                <div class="record_view-item_value">
                    <EventTypeLabel :event-type="authLog.eventType" />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">User</div>
                <div class="record_view-item_value">
                    <RouterLink v-if="authLog.user"
                                :to="{ name: 'admin-user-view', params: { userId: authLog.user.userId } }">
                        {{ authLog.user.email }}
                    </RouterLink>
                    <template v-else-if="authLog.email">{{ authLog.email }}</template>
                    <template v-else>–</template>
                </div>
            </div>
            <div v-if="authLog.impersonatedUser" class="record_view-item">
                <div class="record_view-item_label">Impersonated User</div>
                <div class="record_view-item_value">
                    <RouterLink :to="{
                        name: 'admin-user-view',
                        params: { userId: authLog.impersonatedUser.userId },
                    }">{{ authLog.impersonatedUser.email }}</RouterLink>
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">IP Address</div>
                <div class="record_view-item_value">{{ authLog.ipAddress }}</div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">User Agent</div>
                <div class="record_view-item_value">
                    <template v-if="authLog.userAgent">
                        <div>{{ parsedUserAgent }}</div>
                        <div class="mt-1 text-sm text-gray-500 break-all">{{ authLog.userAgent }}</div>
                    </template>
                    <template v-else>–</template>
                </div>
            </div>
            <div v-if="authLog.route" class="record_view-item">
                <div class="record_view-item_label">Route</div>
                <div class="record_view-item_value">{{ authLog.route }}</div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Error</div>
                <div class="record_view-item_value">{{ authLog.errorMessage ?? '–' }}</div>
            </div>
        </div>
    </template>
</template>

<script setup>
import { computed, ref } from 'vue';
import EventTypeLabel from './component/event_type_label.vue';
import { UAParser } from 'ua-parser-js';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { useQuery } from '@vue/apollo-composable';
import { view as stateMachineConfig } from '@/common/state_machines';
import { GetAuthLogQuery } from '@/admin/queries/auth_log.query.graphql';
import { logError } from '@/common/lib';

const props = defineProps({
    authLogId: {
        type: String,
        required: true,
    },
});

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const authLog = ref(null);

const { onResult, onError } = useQuery(GetAuthLogQuery, { authLogId: props.authLogId });
onResult(({ data }) => {
    if (!data?.AuthLog) {
        sendEvent({ type: 'NOT_FOUND' });

        return;
    }

    authLog.value = data.AuthLog;
    sendEvent({ type: 'LOADED' });
});
onError((error) => {
    logError(error);
    sendEvent({ type: 'ERROR' });
});

const parsedUserAgent = computed(() => {
    if (!authLog.value?.userAgent) {
        return '–';
    }

    const { browser, os } = UAParser(authLog.value.userAgent);
    const parts = [];

    if (browser.name) {
        parts.push(browser.version ? `${browser.name} ${browser.version}` : browser.name);
    }

    if (os.name) {
        parts.push(os.version ? `${os.name} ${os.version}` : os.name);
    }

    return parts.length ? parts.join(' / ') : authLog.value.userAgent;
});
</script>
