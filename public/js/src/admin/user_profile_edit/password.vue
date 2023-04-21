<template>
    <div class="form-wrap p-0">
        <ProfileTabs />

        <form class="p-4" method="post" @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />
            <div v-if="state.matches('saved')"
                 class="alert alert-success mb-4"
                 role="alert">
                <div>
                    Your password has been updated.<br>
                    You will need to login again.
                </div>
                <a :href="loginUrl" class="pl-4">Go to Login</a>
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
                           autocomplete="current-password">
                Current password
                <template #required-msg>Your current password is required.</template>
            </FieldPassword>

            <FieldPassword v-model="newPassword"
                           :v="v$.newPassword"
                           :show-help="true"
                           autocomplete="new-password">
                New password
                <template #required-msg>A new password is required.</template>
            </FieldPassword>
            <FieldPassword v-model="repeatPassword"
                           :v="v$.repeatPassword"
                           autocomplete="new-password">
                New password again
                <template #required-msg>Re-enter your new password.</template>
            </FieldPassword>

            <div class="mb-4 text-sm">After changing your password, you will need to login again.</div>

            <AdminButton :saving="state.matches('saving')"
                         :cancel-to="{ name: 'user-profile-edit' }">
                Change Password
            </AdminButton>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRootStore } from '@/admin/stores/root';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useRouter } from 'vue-router';
import { useMutation } from '@vue/apollo-composable';
import cloneDeep from 'lodash/cloneDeep';
import { sameAs } from '@vuelidate/validators';
import { logError } from '@/common/lib';
import ProfileTabs from './component/tabs';
import FieldPassword from '@/common/field_password_with_errors';
import { ChangePassword } from '../queries/user.mutation.graphql';
import userValidations from './user.validation';

const rootStore = useRootStore();
const router = useRouter();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    strict: true,
    predictableActionArguments: true,
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

const { state, send: sendEvent } = useMachine(stateMachine);

const currentPassword = ref(null);
const newPassword = ref(null);
const repeatPassword = ref(null);

const v$ = useVuelidate({
    currentPassword: cloneDeep(userValidations.currentPassword),
    newPassword: {
        ...cloneDeep(userValidations.newPassword),
        sameAs: sameAs(repeatPassword),
    },
    repeatPassword: cloneDeep(userValidations.repeatPassword),
}, { currentPassword, newPassword, repeatPassword } );

const loginUrl = router.resolve({ name: 'login' }).href;

async function submit () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent('SAVE');

    if (!await v$.value.$validate()) {
        sendEvent('ERROR');
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

        sendEvent('SAVED');

        setTimeout(() => {
            window.location = loginUrl;
        }, 30000);

    } catch (e) {
        logError(e);
        alert('There was a problem saving your password. Please try again later.');

        sendEvent('ERROR');
        window.scrollTo(0, 0);
    }
}
</script>
