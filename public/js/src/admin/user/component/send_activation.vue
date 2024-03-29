<template>
    <div>
        <button v-if="state.matches('ready')"
                :disabled="!allow"
                class="button-link form-action"
                type="button"
                @click="sendReset">Resend Activation Email</button>
        <div v-if="state.matches('sending')" class="form-action">
            Sending…
        </div>
        <div v-if="state.matches('sent')" class="form-action">
            Sent
        </div>
    </div>
</template>

<script setup>
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useMutation } from '@vue/apollo-composable';
import { logError } from '@/common/lib';
import { AdminUserSendActivationMutation } from '@/admin/queries/user.mutation.graphql';

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
    allow: {
        type: Boolean,
        required: true,
    },
});

async function sendReset () {
    if (!props.allow || !state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: 'SEND' });

    try {
        const { mutate: sendUserActivation } = useMutation(AdminUserSendActivationMutation);
        await sendUserActivation({
            userId: props.userId,
        });

        sendEvent({ type: 'SENT' });

        setTimeout(() => {
            sendEvent({ type: 'RESET' });
        }, 3000);

    } catch (e) {
        logError(e);
        alert('There was a problem sending the activation link. Please try again later.');

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }
}
</script>
