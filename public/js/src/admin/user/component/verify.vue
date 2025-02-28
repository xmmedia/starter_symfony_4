<template>
    <div>
        <button v-if="state.matches('ready') && !verified && active"
                class="button text-sm"
                type="button"
                @click="verify">Manually Verify User</button>
        <div v-if="state.matches('verifying')" class="text-sm">
            Verifying…
        </div>
        <div v-if="state.matches('verified')" class="text-sm">
            Verified
        </div>
    </div>
</template>

<script setup>
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { AdminUserVerifyMutation } from '@/admin/queries/user.mutation.graphql';
import { logError } from '@/common/lib';
import { useMutation } from '@vue/apollo-composable';

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                VERIFY: 'verifying',
            },
        },
        verifying: {
            on: {
                COMPLETE: 'verified',
                ERROR: 'ready',
            },
        },
        verified: {
            on: {
                RESET: 'ready',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const emit = defineEmits(['activated', 'verified']);

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
    verified: {
        type: Boolean,
        required: true,
    },
    active: {
        type: Boolean,
        required: true,
    },
});

async function verify () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: 'VERIFY' });

    try {
        const { mutate: sendUserVerify } = useMutation(AdminUserVerifyMutation);
        await sendUserVerify({
            userId: props.userId,
        });

        emit('verified');
        sendEvent({ type: 'COMPLETE' });

        delayedReset();

    } catch (e) {
        logError(e);
        alert('There was a problem verifying the user. Please try again later.');

        sendEvent({ type: 'ERROR' });
    }
}

function delayedReset () {
    setTimeout(() => {
        sendEvent({ type: 'RESET' });
    }, 3000);
}
</script>
