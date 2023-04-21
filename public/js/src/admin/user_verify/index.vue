<template>
    <div>
        <form v-if="showForm"
              class="form-wrap"
              method="post"
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
                           autocomplete="new-password" />
            <FieldPassword v-model="repeatPassword"
                           :v="v$.repeatPassword"
                           autocomplete="new-password">Password again</FieldPassword>

            <AdminButton :saving="state.matches('submitting')"
                         :cancel-to="{ name: 'login' }">
                Activate
                <template #cancel>
                    <RouterLink :to="{ name: 'login' }" class="form-action">Login</RouterLink>
                </template>
                <template #saving>Activating…</template>
            </AdminButton>
        </form>

        <div v-if="state.matches('verified')" class="alert alert-success max-w-lg" role="alert">
            Your account is now active.
            <RouterLink :to="{ name: 'login' }" class="pl-4">Login</RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useStore } from 'vuex';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useRoute, useRouter } from 'vue-router';
import { required, sameAs } from '@vuelidate/validators';
import FieldPassword from '@/common/field_password_with_errors';
import { UserVerify } from '@/admin/queries/user.mutation.graphql';
import userValidation from '@/admin/validation/user';
import cloneDeep from 'lodash/cloneDeep';
import { hasGraphQlError, logError } from '@/common/lib';
import { useMutation } from '@vue/apollo-composable';

const store = useStore();
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
                SUBMITTED: 'verified',
                ERROR: 'ready',
            },
        },
        verified: {
            type: 'final',
        },
    },
});

const { state, send: sendEvent } = useMachine(stateMachine);

const v$ = useVuelidate({
    password: {
        ...cloneDeep(userValidation.password),
    },
    repeatPassword: {
        required,
        sameAs: sameAs('password'),
    },
});

const invalidToken = ref(false);
const tokenExpired = ref(false);

const password = ref(null);
const repeatPassword = ref(null);

const showForm = computed(() => !state.value.done);

onMounted(() => {
    if (store.getters.loggedIn) {
        router.replace({ name: 'login' });
    }
});

async function submit () {
    sendEvent('SUBMIT');

    if (!await v$.value.$validate()) {
        sendEvent('ERROR');
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendUserVerify } = useMutation(UserVerify);
        await sendUserVerify({
            token: route.params.token,
            password: password.value,
        });

        password.value = null;
        repeatPassword.value = null;
        invalidToken.value = false;
        tokenExpired.value = false;

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
        alert('There was a problem activating your account. Please try again later.');
    }
}
</script>
