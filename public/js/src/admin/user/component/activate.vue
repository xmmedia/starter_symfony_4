<template>
    <div>
        <button v-if="state.matches('ready')"
                class="button text-sm"
                type="button"
                @click="toggleActive">{{ activeButtonText }}</button>

        <div v-if="state.matches('activating')" class="text-sm">
            Activating…
        </div>
        <div v-if="state.matches('activated')" class="text-sm">
            Activated
        </div>
        <div v-if="state.matches('deactivating')" class="text-sm">
            Deactivating…
        </div>
        <div v-if="state.matches('deactivated')" class="text-sm">
            Deactivated
        </div>
        <div v-if="state.matches('verifying')" class="text-sm">
            Verifying…
        </div>
        <div v-if="state.matches('verified')" class="text-sm">
            Verified
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import {
    AdminUserActivateMutation,
    AdminUserVerifyMutation,
} from '@/admin/queries/user.mutation.graphql';
import { logError } from '@/common/lib';
import { useMutation } from '@vue/apollo-composable';

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                ACTIVATE: 'activating',
                DEACTIVATE: 'deactivating',
                VERIFY: 'verifying',
            },
        },
        activating: {
            on: {
                COMPLETE: 'activated',
                ERROR: 'ready',
            },
        },
        activated: {
            on: {
                RESET: 'ready',
            },
        },
        deactivating: {
            on: {
                COMPLETE: 'deactivated',
                ERROR: 'ready',
            },
        },
        deactivated: {
            on: {
                RESET: 'ready',
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

const emit = defineEmits(['activated', 'deactivated', 'verified']);

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

const activeButtonText = computed(() => props.active ? 'Deactivate User' : 'Activate User');

async function toggleActive () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: props.active ? 'DEACTIVATE' : 'ACTIVATE' });

    try {
        const { mutate: sendUserActivate } = useMutation(AdminUserActivateMutation);
        await sendUserActivate({
            user: {
                userId: props.userId,
                action: props.active ? 'deactivate' : 'activate',
            },
        });

        emit(props.active ? 'deactivated' : 'activated');
        sendEvent({ type: 'COMPLETE' });

        delayedReset();

    } catch (e) {
        logError(e);
        alert('There was a problem toggling the active state. Please try again later.');

        sendEvent({ type: 'ERROR' });
    }
}

async function verify () {
    if (!allowSave.value) {
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
