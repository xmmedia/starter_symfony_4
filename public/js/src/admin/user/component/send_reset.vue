<template>
    <div>
        <button v-if="state.matches('ready')"
                :disabled="!allow"
                class="button-link form-action"
                type="button"
                @click="sendReset">Send Password Reset</button>
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
import { AdminUserSendResetMutation } from '@/admin/queries/admin/user.mutation.graphql';

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    strict: true,
    predictableActionArguments: true,
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

const { state, send: sendEvent } = useMachine(stateMachine);

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

    sendEvent('SEND');

    try {
        const { mutate: sendUserReset } = useMutation(AdminUserSendResetMutation);
        await sendUserReset({
            userId: props.userId,
        });

        sendEvent('SENT');

        setTimeout(() => {
            sendEvent('RESET');
        }, 3000);

    } catch (e) {
        logError(e);
        alert('There was a problem sending the reset. Please try again later.');

        sendEvent('ERROR');
        window.scrollTo(0, 0);
    }
}
</script>
