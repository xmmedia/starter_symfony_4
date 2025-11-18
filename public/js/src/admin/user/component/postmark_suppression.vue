<template>
    <LoadingSpinner v-if="state.matches('loading')" class="text-gray-500 italic">
        Checking email deliverability status...
    </LoadingSpinner>
    <div v-else-if="state.matches('error')" class="text-gray-500 italic">
        Unable to check email status.
    </div>
    <template v-else-if="state.matches('loaded')">
        <div v-if="suppressionData.suppressed"
             class="mt-2 px-4 py-3 text-red-900 bg-red-50 border border-red-200 rounded-md">
            <h4 class="flex gap-x-2 items-end mt-0">
                <AdminIcon icon="warning" class="record_list-icon w-8 h-8" />
                Cannot Send Emails to this Address
            </h4>
            <div class="grid gap-y-2 text-sm">
                <div>
                    The site cannot send emails to this address
                    because it has been added to the suppression list in the email provider.
                </div>
                <div>Reason: {{ suppressionData.reasonHuman }}</div>
                <div>Added: <LocalTime :datetime="suppressionData.dateAdded" /></div>
                <div>
                    <a :href="suppressionData.espUrl" target="_blank">View in Postmark</a>
                </div>
            </div>
        </div>
        <div v-else class="flex items-center gap-x-2" title="The site can send emails to this email address.">
            <AdminIcon icon="check" class="record_list-icon text-green-500" />
            <span>Email deliverable</span>
        </div>
    </template>
</template>

<script setup>
import { ref } from 'vue';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { view as stateMachineConfig } from '@/common/state_machines';
import { useQuery } from '@vue/apollo-composable';
import { GetEmailSuppressionQuery } from '@/admin/queries/user.query.graphql';
import { logError } from '@/common/lib';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
});

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const suppressionData = ref({
    suppressed: false,
    reasonHuman: null,
    dateAdded: null,
    espUrl: '',
});

const { onResult, onError } = useQuery(GetEmailSuppressionQuery, { email: props.email });

onResult(({ data }) => {
    if (data?.EmailSuppression) {
        suppressionData.value = data.EmailSuppression;
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
