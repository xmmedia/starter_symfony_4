<template>
    <PublicFormWrap>
        <template #heading>Password Reset</template>

        <PublicAlert v-if="state.matches('changed')" class="alert-success">
            Your password has been reset.
            <RouterLink :to="{ name: 'login' }" class="pl-4">Sign In</RouterLink>
        </PublicAlert>

        <form v-if="showForm" method="post" @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />

            <FieldError v-if="invalidToken" class="mb-8">
                Your reset link is invalid or has expired.
                Please try clicking the button again or copying the link.
                Or you can <RouterLink :to="{ name: 'user-recover-initiate' }">try again</RouterLink>.
            </FieldError>
            <FieldError v-if="tokenExpired" class="mb-8">
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
                           autocomplete="new-password"
                           icon-component="PublicIcon">New password</FieldPassword>
            <FieldPassword v-model="repeatPassword"
                           :v="v$.repeatPassword"
                           autocomplete="new-password"
                           icon-component="PublicIcon">New password again</FieldPassword>

            <FormButton :saving="state.matches('submitting')" wrapper-classes="form_button-wrap">
                Set Password
                <template #cancel>
                    <RouterLink :to="{ name: 'login' }" class="form-action">Return to Sign In</RouterLink>
                </template>
            </FormButton>
        </form>
    </PublicFormWrap>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useRouter } from 'vue-router';
import { useMutation } from '@vue/apollo-composable';
import { helpers, required } from '@vuelidate/validators';
import { apolloClient } from '@/common/apollo';
import { hasGraphQlError, logError } from '@/common/lib';
import FieldPassword from '@/common/field_password_with_errors.vue';
import PublicFormWrap from '@/common/public_form_wrap.vue';
import PublicAlert from '@/common/public_alert.vue';
import { UserPasswordAllowed } from '@/user/queries/user.query.graphql';
import { UserRecoverReset } from '@/user/queries/user.mutation.graphql';
import userValidation from '@/common/validation/user';

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
                SUBMITTED: 'changed',
                ERROR: 'ready',
            },
        },
        changed: {
            type: 'final',
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const invalidToken = ref(false);
const tokenExpired = ref(false);
const newPassword = ref(null);
const repeatPassword = ref(null);

const userValidationGenerated = userValidation();
const v$ = useVuelidate({
    newPassword: {
        ...userValidationGenerated.password,
        strength: helpers.withAsync(async function (value) {
            if (!userValidationGenerated.password.strength(value)) {
                return false;
            }

            try {
                // use apolloClient directly so we can async
                const result = await apolloClient.query({
                    query: UserPasswordAllowed,
                    variables: {
                        newPassword: value,
                    },
                });

                return result.data.UserRecoverResetPasswordStrength.allowed;
            } catch (e) {
                logError(e);

                // more than likely it's okay and if it isn't the mutation will fail
                return true;
            }
        }),
    },
    repeatPassword: {
        required,
    },
}, { newPassword, repeatPassword });

const showForm = computed(() => !state.value.matches('changed'));

const submit = async () => {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: 'SUBMIT' });

    if (!await v$.value.$validate()) {
        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendRecoverReset } = useMutation(UserRecoverReset);
        await sendRecoverReset({
            newPassword: newPassword.value,
        });

        newPassword.value = null;
        repeatPassword.value = null;
        invalidToken.value = false;
        tokenExpired.value = false;
        v$.value.$reset();

        sendEvent({ type: 'SUBMITTED' });

        setTimeout(() => {
            router.push({ name: 'login' });
        }, 5000);
    } catch (e) {
        const showError = () => {
            alert('There was a problem saving your password. Please try again later.');
        };

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
};
</script>
