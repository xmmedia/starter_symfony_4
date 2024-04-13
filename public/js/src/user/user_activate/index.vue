<template>
    <div>
        <form v-if="showForm"
              class="form-wrap"
              method="post"
              novalidate
              @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />
            <FieldError v-if="invalidToken" class="mb-4">
                Your activation link is invalid.
                Please try clicking the button again or copying the link.
            </FieldError>
            <FieldError v-if="tokenExpired" class="mb-4">
                Your link has expired. Please contact an administrator.
            </FieldError>

            <p :class="{ 'mt-0' : !v$.$error && !invalidToken && !tokenExpired }">
                To activate your account, enter a password below.
            </p>

            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <FieldPassword v-model="password"
                           :v="v$.password"
                           :show-help="true"
                           autocomplete="new-password"
                           icon-component="PublicIcon" />
            <FieldPassword v-model="repeatPassword"
                           :v="v$.repeatPassword"
                           autocomplete="new-password"
                           icon-component="PublicIcon">Password again</FieldPassword>

            <FormButton :saving="state.matches('submitting')"
                        :cancel-to="{ name: 'login' }">
                Activate
                <template #cancel>
                    <RouterLink :to="{ name: 'login' }" class="form-action">Sign In</RouterLink>
                </template>
                <template #saving>Activating…</template>
            </FormButton>
        </form>

        <div v-if="state.matches('verified')" class="alert alert-success max-w-lg" role="alert">
            Your account is now active.
            <RouterLink :to="{ name: 'login' }" class="pl-4">Sign In</RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRootStore } from '@/user/stores/root';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useRouter } from 'vue-router';
import { required, sameAs } from '@vuelidate/validators';
import FieldPassword from '@/common/field_password_with_errors';
import { UserActivate } from '@/user/queries/user.mutation.graphql';
import userValidation from '@/common/validation/user';
import { hasGraphQlError, logError } from '@/common/lib';
import { useMutation } from '@vue/apollo-composable';

const rootStore = useRootStore();
const router = useRouter();

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
                SUBMITTED: 'verified',
                ERROR: 'ready',
            },
        },
        verified: {
            type: 'final',
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const invalidToken = ref(false);
const tokenExpired = ref(false);
const password = ref(null);
const repeatPassword = ref(null);

const showForm = computed(() => state.value.matches('verfieid'));

const v$ = useVuelidate({
    password: userValidation().password,
    repeatPassword: {
        required,
        sameAs: sameAs(password),
    },
}, { password, repeatPassword });

onMounted(() => {
    if (rootStore.loggedIn) {
        router.replace({ name: 'login' });
    }
});

async function submit () {
    sendEvent({ type: 'SUBMIT' });

    if (!await v$.value.$validate()) {
        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendUserActivate } = useMutation(UserActivate);
        await sendUserActivate({
            password: password.value,
        });

        password.value = null;
        repeatPassword.value = null;
        invalidToken.value = false;
        tokenExpired.value = false;

        sendEvent({ type: 'SUBMITTED' });

        setTimeout(() => {
            router.push({ name: 'login' });
        }, 5000);

    } catch (e) {
        if (hasGraphQlError(e)) {
            if (404 === e.graphQLErrors[0].code) {
                invalidToken.value = true;
            } else if (405 === e.graphQLErrors[0].code) {
                tokenExpired.value = true;
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
        alert('There was a problem activating your account. Please try again later.');
    }
}
</script>
