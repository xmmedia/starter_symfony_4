<template>
    <Teleport to="#header-actions">
        <div class="header-secondary_actions">
            <RouterLink :to="{ name: 'admin-messenger-queue' }">Return to list</RouterLink>
        </div>
    </Teleport>

    <LoadingSpinner v-if="state.matches('loading')">
        Loading…
    </LoadingSpinner>
    <div v-else-if="state.matches('not_found')" class="italic text-center">
        <p>The record could not be found.</p>
        <p><RouterLink :to="{ name: 'admin-messenger-queue' }">Return to list</RouterLink></p>
    </div>
    <div v-else-if="state.matches('error')" class="italic text-center">
        <p>There was a problem loading this record. Please try again later.</p>
        <p><RouterLink :to="{ name: 'admin-messenger-queue' }">Return to list</RouterLink></p>
    </div>

    <template v-if="state.matches('loaded')">
        <Teleport to="#header-page-title">
            <span>Queue Message #{{ message.id }}</span>
        </Teleport>

        <div class="record_view-item_wrap">
            <div class="record_view-item">
                <div class="record_view-item_label">ID</div>
                <div class="record_view-item_value">{{ message.id }}</div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Queue</div>
                <div class="record_view-item_value">{{ message.queueName }}</div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Message Class</div>
                <!-- flex is to vertically center -->
                <div class="record_view-item_value flex items-center">
                    <div class="font-mono text-sm break-all">{{ message.messageClass ?? '–' }}</div>
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Created At</div>
                <div class="record_view-item_value">
                    <LocalTime :datetime="message.createdAt" />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Available At</div>
                <div class="record_view-item_value">
                    <LocalTime :datetime="message.availableAt" />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Delivered At</div>
                <div class="record_view-item_value">
                    <LocalTime v-if="message.deliveredAt" :datetime="message.deliveredAt" />
                    <span v-else class="italic text-gray-400">Not yet delivered</span>
                </div>
            </div>
        </div>
    </template>
</template>

<script setup>
import { ref } from 'vue';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { useQuery } from '@vue/apollo-composable';
import { view as stateMachineConfig } from '@/common/state_machines';
import { GetMessengerQueueMessageQuery } from '@/admin/queries/messenger_queue_message.query.graphql';
import { logError } from '@/common/lib';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const message = ref(null);

const { onResult, onError } = useQuery(GetMessengerQueueMessageQuery, { id: +props.id });
onResult(({ data }) => {
    if (!data?.MessengerQueueMessage) {
        sendEvent({ type: 'NOT_FOUND' });

        return;
    }

    message.value = data.MessengerQueueMessage;
    sendEvent({ type: 'LOADED' });
});
onError((error) => {
    logError(error);
    sendEvent({ type: 'ERROR' });
});
</script>
