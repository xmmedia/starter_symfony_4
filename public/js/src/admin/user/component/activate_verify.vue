<template>
    <div>
        <template v-if="state.matches('ready')">
            <button v-if="verified"
                    :disabled="!allow"
                    class="button-link form-action"
                    type="button"
                    @click="toggleActive">{{ activeButtonText }}</button>
            <button v-else
                    :disabled="!allow"
                    class="button-link form-action"
                    type="button"
                    @click="verify">Manually Verify User</button>
        </template>

        <div v-if="state.matches('activating')" class="form-action">
            Activating…
        </div>
        <div v-if="state.matches('activated')" class="form-action">
            Activated
        </div>
        <div v-if="state.matches('deactivating')" class="form-action">
            Deactivating…
        </div>
        <div v-if="state.matches('deactivated')" class="form-action">
            Deactivated
        </div>
        <div v-if="state.matches('verifying')" class="form-action">
            Verifying…
        </div>
        <div v-if="state.matches('verified')" class="form-action">
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
} from '@/admin/queries/admin/user.mutation.graphql';
import { logError } from '@/common/lib';
import { useMutation } from '@vue/apollo-composable';

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    strict: true,
    predictableActionArguments: true,
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

const { state, send: sendEvent } = useMachine(stateMachine);

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
    allow: {
        type: Boolean,
        required: true,
    },
});

const allowSave = computed(() => props.allow && state.value.matches('ready'));
const activeButtonText = computed(() => props.active ? 'Deactivate User' : 'Activate User');

async function toggleActive () {
    if (!allowSave.value) {
        return;
    }

    sendEvent(props.active ? 'DEACTIVATE' : 'ACTIVATE');

    try {
        const { mutate: sendUserActivate } = useMutation(AdminUserActivateMutation);
        await sendUserActivate({
            user: {
                userId: props.userId,
                action: props.active ? 'deactivate' : 'activate',
            },
        });

        emit(props.active ? 'deactivated' : 'activated');
        sendEvent('COMPLETE');

        delayedReset();

    } catch (e) {
        logError(e);
        alert('There was a problem toggling the active state. Please try again later.');

        sendEvent('ERROR');
        window.scrollTo(0, 0);
    }
}

async function verify () {
    if (!allowSave.value) {
        return;
    }

    sendEvent('VERIFY');

    try {
        const { mutate: sendUserVerify } = useMutation(AdminUserVerifyMutation);
        await sendUserVerify({
            user: {
                userId: props.userId,
            },
        });

        emit('verified');
        sendEvent('COMPLETE');

        delayedReset();

    } catch (e) {
        logError(e);
        alert('There was a problem verifying the user. Please try again later.');

        sendEvent('ERROR');
        window.scrollTo(0, 0);
    }
}

function delayedReset () {
    setTimeout(() => {
        sendEvent('RESET');
    }, 3000);
}
</script>
