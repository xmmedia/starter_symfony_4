<template>
    <PublicWrap>
        <template #heading>Password Reset</template>

        <PublicAlert v-if="state.matches('requested')" class="alert-success flex-wrap">
            A password reset link has been sent by email.
            Please follow the instructions within the email to reset your password.
            <RouterLink v-if="!rootStore.loggedIn"
                        :to="{ name: 'login' }"
                        class="mt-4">Return to Sign In
            </RouterLink>
        </PublicAlert>

        <form v-if="showForm" method="post" @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />
            <FieldError v-if="notFound" class="mb-8">
                An account with that email cannot be found.
            </FieldError>
            <FieldError v-if="tooMany" class="mb-8">
                You have already requested a reset password email.
                Please check your email or try again soon.
            </FieldError>

            <FieldEmail v-model="email"
                        :v="v$.email"
                        autofocus
                        autocomplete="username email"
                        class="field-wrap-normal"
                        @update:model-value="changed">
                Please enter your email address to search for your account:
            </FieldEmail>

<!-- @todo .form_button-wrap still needed -->
            <div class="form_button-wrap">
                <FormButton :saving="state.matches('submitting')"
                            button-classes="w-full"
                            wrapper-classes="flex flex-wrap">
                    Find Account
                    <template #cancel>
                        <RouterLink v-if="!rootStore.loggedIn"
                                    :to="{ name: 'login' }"
                                    class="form-action block mt-4">Return to Sign In
                        </RouterLink>
                    </template>
                    <template #saving>Requesting…</template>
                </FormButton>
            </div>
        </form>
    </PublicWrap>
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
import PublicWrap from '@/common/public_wrap.vue';
import PublicAlert from '@/common/public_alert.vue';
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

const showForm = computed(() => !state.value.matches('requested'));

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
