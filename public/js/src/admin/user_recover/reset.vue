<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />
            <FieldError v-if="invalidToken" class="mb-4">
                Your reset link is invalid or has expired.
                Please try clicking the button again or copying the link.
                Or you can <RouterLink :to="{ name: 'user-recover-initiate' }">try again</RouterLink>.
            </FieldError>
            <FieldError v-if="tokenExpired" class="mb-4">
                Your link has expired.
                Please try
                <RouterLink :to="{ name: 'user-recover-initiate' }">
                    requesting a new password reset link
                </RouterLink>.
            </FieldError>

            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       value=""
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <FieldPassword v-model="newPassword"
                           :v="v$.newPassword"
                           :show-help="true"
                           autocomplete="new-password">New password</FieldPassword>
            <FieldPassword v-model="repeatPassword"
                           :v="v$.repeatPassword"
                           autocomplete="new-password">New password again</FieldPassword>

            <AdminButton :saving="state.matches('submitting')">
                Set Password
                <template #cancel>
                    <RouterLink :to="{ name: 'login' }" class="form-action">Return to Login</RouterLink>
                </template>
            </AdminButton>
        </form>

        <div v-if="state.matches('changed')" class="alert alert-success max-w-lg" role="alert">
            Your password has been reset.
            <RouterLink :to="{ name: 'login' }" class="pl-4">Login</RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRootStore } from '@/admin/stores/root';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useRoute, useRouter } from 'vue-router';
import { useMutation } from '@vue/apollo-composable';
import cloneDeep from 'lodash/cloneDeep';
import { required } from '@vuelidate/validators';
import { hasGraphQlError, logError } from '@/common/lib';
import FieldPassword from '@/common/field_password_with_errors';
import { UserRecoverReset } from '../queries/user.mutation.graphql';
import userValidation from '@/admin/validation/user';

const rootStore = useRootStore();
const router = useRouter();
const route = useRoute();

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
                SUBMITTED: 'changed',
                ERROR: 'ready',
            },
        },
        changed: {
            type: 'final',
        },
    },
});

const { state, send: sendEvent } = useMachine(stateMachine);

const invalidToken = ref(false);
const tokenExpired = ref(false);
const newPassword = ref(null);
const repeatPassword = ref(null);

const v$ = useVuelidate({
    newPassword: {
        ...cloneDeep(userValidation.password),
    },
    repeatPassword: {
        required,
    },
}, { newPassword, repeatPassword });

const showForm = computed(() => !state.value.done);

onMounted(() => {
    if (rootStore.loggedIn) {
        router.replace({ name: 'login' });
    }
});

async function submit () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent('SUBMIT');

    if (!await v$.value.$validate()) {
        sendEvent('ERROR');
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendRecoverReset } = useMutation(UserRecoverReset);
        await sendRecoverReset({
            token: route.params.token,
            newPassword: newPassword.value,
        });

        newPassword.value = null;
        repeatPassword.value = null;
        invalidToken.value = false;
        tokenExpired.value = false;
        v$.value.$reset();

        sendEvent('SUBMITTED');

        setTimeout(() => {
            window.location = router.resolve({ name: 'login' }).href;
        }, 5000);
    } catch (e) {
        if (hasGraphQlError(e)) {
            if (e.graphQLErrors[0].code === 404) {
                invalidToken.value = true;
            } else if (e.graphQLErrors[0].code === 405) {
                tokenExpired.value = true;
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
        alert('There was a problem saving your password. Please try again later.');
    }
}
</script>
