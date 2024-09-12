<template>
    <div>
        <button v-if="state.matches('ready')"
                class="button text-sm"
                type="button"
                @click="sendReset">Send Password Reset</button>
        <div v-if="state.matches('sending')" class="text-sm">
            Sending…
        </div>
        <div v-if="state.matches('sent')" class="text-sm">
            Sent
        </div>
    </div>
</template>

<script setup>
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useMutation } from '@vue/apollo-composable';
import { hasGraphQlError, logError } from '@/common/lib';
import { AdminUserSendResetMutation } from '@/admin/queries/user.mutation.graphql';

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                SEND: 'sending',
            },
        },
        sending: {
            on: {
                SENT: 'sent',
                ERROR: 'ready',
            },
        },
        sent: {
            on: {
                RESET: 'ready',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
});

async function sendReset () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: 'SEND' });

    try {
        const { mutate: sendUserReset } = useMutation(AdminUserSendResetMutation);
        await sendUserReset({
            userId: props.userId,
        });

        sendEvent({ type: 'SENT' });

        setTimeout(() => {
            sendEvent({ type: 'RESET' });
        }, 3000);

    } catch (e) {
        if (hasGraphQlError(e) && 429 === e.graphQLErrors[0].code) {
            alert('Too many password requests have been sent. Only 1 password reset can be requested every hour. Please try again later.');
            sendEvent({ type: 'ERROR' });

            return;
        }

        logError(e);
        alert('There was a problem sending the reset. Please try again later.');

        sendEvent({ type: 'ERROR' });
    }
}
</script>
