<template>
    <div class="form-wrap my-8 p-0">
        <ProfileTabs />

        <form class="p-4" method="post" novalidate @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />
            <div v-if="state.matches('saved')"
                 class="alert alert-success mb-4"
                 role="alert">
                <div>
                    Your password has been updated.<br>
                    You will need to login again.
                </div>
                <a href="/login" class="pl-4">Go to Sign In</a>
            </div>

            <!-- this is for the browser so it can generate a new password -->
            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       :value="rootStore.user.email"
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <FieldPassword v-model="currentPassword"
                           :v="v$.currentPassword"
                           :show-help="true"
                           autocomplete="current-password"
                           icon-component="PublicIcon">
                Current password
                <template #required-msg>Your current password is required.</template>
                <template #help>
                    If you don't know your current password,
                    <RouterLink :to="{ name: 'user-recover-initiate' }">click here to reset it</RouterLink>.
                </template>
            </FieldPassword>

            <FieldPassword v-model="newPassword"
                           :v="v$.newPassword"
                           :show-help="true"
                           autocomplete="new-password"
                           icon-component="PublicIcon">
                New password
                <template #required-msg>A new password is required.</template>
            </FieldPassword>
            <FieldPassword v-model="repeatPassword"
                           :v="v$.repeatPassword"
                           autocomplete="new-password"
                           icon-component="PublicIcon">
                New password again
                <template #required-msg>Re-enter your new password.</template>
            </FieldPassword>

            <div class="mb-4 text-sm">After changing your password, you will need to sign in again.</div>

            <FormButton :saving="state.matches('saving')"
                        :cancel-to="{ name: 'user-profile-edit' }">
                Change Password
            </FormButton>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRootStore } from '@/user/stores/root';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useMutation } from '@vue/apollo-composable';
import { sameAs } from '@vuelidate/validators';
import { logError } from '@/common/lib';
import ProfileTabs from './component/tabs.vue';
import FieldPassword from '@/common/field_password_with_errors.vue';
import { ChangePassword } from '../../user/queries/user.mutation.graphql';
import userValidation from './user.validation';

const rootStore = useRootStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                SAVE: 'saving',
            },
        },
        saving: {
            on: {
                SAVED: 'saved',
                ERROR: 'ready',
            },
        },
        saved: {
            on: {
                RESET: 'ready',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const currentPassword = ref(null);
const newPassword = ref(null);
const repeatPassword = ref(null);

const userValidations = userValidation();
const v$ = useVuelidate({
    currentPassword: userValidations.currentPassword,
    newPassword: {
        ...userValidations.newPassword,
        sameAs: sameAs(repeatPassword),
    },
    repeatPassword: userValidations.repeatPassword,
}, {
    currentPassword,
    newPassword,
    repeatPassword,
    email: rootStore.user.email,
    firstName: rootStore.user.firstName,
    lastName: rootStore.user.lastName,
});

async function submit () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: 'SAVE' });

    if (!await v$.value.$validate()) {
        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendChangePassword } = useMutation(ChangePassword);
        await sendChangePassword({
            user: {
                currentPassword: currentPassword.value,
                newPassword: newPassword.value,
            },
        });

        currentPassword.value = null;
        newPassword.value = null;
        repeatPassword.value = null;
        v$.value.$reset();

        sendEvent({ type: 'SAVED' });

        setTimeout(() => {
            window.location = '/login';
        }, 30000);

    } catch (e) {
        logError(e);
        alert('There was a problem saving your password. Please try again later.');

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }
}
</script>
