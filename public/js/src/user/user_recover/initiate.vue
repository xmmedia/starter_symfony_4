<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit"
              novalidate>
            <FormError v-if="v$.$error && v$.$invalid" />
            <FieldError v-if="notFound" class="mb-4">
                An account with that email cannot be found.
            </FieldError>
            <FieldError v-if="tooMany" class="mb-4">
                You have already requested a reset password email.
                Please check your email or try again soon.
            </FieldError>

            <FieldEmail v-model="email"
                        :v="v$.email"
                        autofocus
                        autocomplete="username email"
                        @update:model-value="changed">
                Please enter your email address to search for your account:
            </FieldEmail>

            <FormButton :saving="state.matches('submitting')">
                Find Account
                <template #cancel>
                    <RouterLink v-if="!rootStore.loggedIn"
                                :to="{ name: 'login' }"
                                class="form-action">Return to Sign In</RouterLink>
                </template>
                <template #saving>Requesting…</template>
            </FormButton>
        </form>

        <div v-if="state.matches('requested')" class="alert alert-success max-w-lg" role="alert">
            A password reset link has been sent by email.
            Please follow the instructions within the email to reset your password.
            <RouterLink v-if="!rootStore.loggedIn"
                        :to="{ name: 'login' }"
                        class="w-64 pl-4 text-sm">Return to Sign In</RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRootStore } from '@/user/stores/root';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useMutation } from '@vue/apollo-composable';
import { email as emailValidator, required } from '@vuelidate/validators';
import { hasGraphQlError, logError } from '@/common/lib';
import FieldEmail from '@/common/field_email';
import { UserRecoverInitiate } from '../../user/queries/user.mutation.graphql';

const rootStore = useRootStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                SUBMIT: 'submitting',
            },
        },
        submitting: {
            on: {
                SUBMITTED: 'requested',
                ERROR: 'ready',
            },
        },
        requested: {
            type: 'final',
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const notFound = ref(false);
const tooMany = ref(false);
const email = ref(null);

const v$ = useVuelidate({
    email: {
        required,
        email: emailValidator,
    },
}, { email });

const showForm = computed(() => !state.value.done);

onMounted(() => {
    if (rootStore.loggedIn) {
        email.value = rootStore.user.email;
    }
});

async function submit () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: 'SUBMIT' });
    notFound.value = false;
    tooMany.value = false;

    if (!await v$.value.$validate()) {
        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendRecoverInitiate } = useMutation(UserRecoverInitiate);
        await sendRecoverInitiate({
            email: email.value,
        });

        email.value = null;
        v$.value.$reset();

        sendEvent({ type: 'SUBMITTED' });

    } catch (e) {
        if (hasGraphQlError(e)) {
            if (404 === e.graphQLErrors[0].code) {
                notFound.value = true;
            } else if (429 === e.graphQLErrors[0].code) {
                tooMany.value = true;
            } else {
                logError(e);
                showError();
            }
        } else {
            logError(e);
            showError();
        }

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }

    function showError () {
        alert('There was a problem requesting a password reset. Please try again later.');
    }
}

function changed () {
    notFound.value = false;
}
</script>
