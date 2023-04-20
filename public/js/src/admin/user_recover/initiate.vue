<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />
            <FieldError v-if="notFound" class="mb-4">
                An account with that email cannot be found.
            </FieldError>

            <FieldEmail v-model="email"
                        :v="v$.email"
                        autofocus
                        autocomplete="username email"
                        @update:modelValue="changed">
                Please enter your email address to search for your account:
            </FieldEmail>

            <AdminButton :saving="state.matches('submitting')">
                Find Account
                <template #cancel>
                    <RouterLink :to="{ name: 'login' }" class="form-action">Return to Login</RouterLink>
                </template>
                <template #saving>Requesting…</template>
            </AdminButton>
        </form>

        <div v-if="state.matches('requested')" class="alert alert-success max-w-lg" role="alert">
            A password reset link has been sent by email.
            Please follow the instructions within the email to reset your password.
            <RouterLink :to="{ name: 'login' }" class="w-64 pl-4 text-sm">Return to Login</RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useRouter } from 'vue-router';
import { useMutation } from '@vue/apollo-composable';
import { email as emailValidator, required } from '@vuelidate/validators';
import { hasGraphQlError, logError } from '@/common/lib';
import FieldEmail from '@/common/field_email';
import { UserRecoverInitiate } from '../queries/user.mutation.graphql';

const store = useStore();
const router = useRouter();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    strict: true,
    predictableActionArguments: true,
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

const { state, send: sendEvent } = useMachine(stateMachine);

const notFound = ref(false);
const email = ref(null);

const v$ = useVuelidate({
    email: {
        required,
        email: emailValidator,
    },
}, { email });

const showForm = computed(() => !store.state.done);

onMounted(() => {
    if (store.getters.loggedIn) {
        router.replace({ name: 'login' });
    }
});

async function submit () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent('SUBMIT');
    notFound.value = false;

    if (!await v$.value.$validate()) {
        sendEvent('ERROR');
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

        sendEvent('SUBMITTED');

    } catch (e) {
        if (hasGraphQlError(e)) {
            if (e.graphQLErrors[0].code === 404) {
                notFound.value = true;
            } else {
                logError(e);
                showError();
            }
        } else {
            logError(e);
            showError();
        }

        sendEvent('ERROR');
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
