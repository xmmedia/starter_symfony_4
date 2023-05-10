<template>
    <div class="form-wrap">
        <Portal to="header-actions">
            <div class="header-secondary_actions">
                <RouterLink :to="{ name: 'admin-user' }">Return to list</RouterLink>
            </div>
        </Portal>

        <h2 class="mt-0">Edit User</h2>

        <LoadingSpinner v-if="state.matches('loading')">
            Loading user…
        </LoadingSpinner>
        <div v-else-if="state.matches('error')" class="italic text-center">
            There was a problem loading the user. Please try again later.
        </div>

        <form v-else-if="showForm" method="post" @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />

            <FieldEmail :model-value="email"
                        :v="v$.email"
                        autocomplete="off"
                        autofocus
                        @update:modelValue="setEmailDebounce" />

            <FieldPassword v-model="password"
                           :v="v$.password"
                           checkbox-label="Change password"
                           @set-password="setPassword = $event" />

            <FieldInput v-model.trim="firstName" :v="v$.firstName">First name</FieldInput>
            <FieldInput v-model.trim="lastName" :v="v$.lastName">Last name</FieldInput>

            <FieldRole v-model="role" :v="v$.role" />

            <AdminButton :saving="state.matches('ready.saving')"
                         :saved="state.matches('ready.saved')"
                         :cancel-to="{ name: 'admin-user' }">
                Update User
            </AdminButton>

            <ul class="form-extra_actions">
                <li>
                    <activate-verify :user-id="userId"
                                     :verified="verified"
                                     :active="active"
                                     :allow="allowSave"
                                     @activated="active = true"
                                     @deactivated="active = false"
                                     @verified="verified = true" />
                </li>
                <li v-if="!verified">
                    <SendActivation :user-id="userId" :allow="allowSave" />
                </li>
                <li v-if="active">
                    <SendReset :user-id="userId" :allow="allowSave" />
                </li>
            </ul>
        </form>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useMutation, useQuery } from '@vue/apollo-composable';
import cloneDeep from 'lodash/cloneDeep';
import debounce from 'lodash/debounce';
import { logError } from '@/common/lib';
import FieldEmail from '@/common/field_email';
import FieldPassword from './component/password';
import FieldInput from '@/common/field_input';
import FieldRole from './component/role';
import ActivateVerify from './component/activate_verify';
import SendActivation from './component/send_activation';
import SendReset from './component/send_reset';
import { GetUserQuery } from '../queries/user.query.graphql';
import { AdminUserUpdateMutation } from '../queries/admin/user.mutation.graphql';
import userValidations from './user.validation';
import { requiredIf } from '@vuelidate/validators';

const router = useRouter();

const stateMachine = createMachine({
    id: 'component',
    initial: 'loading',
    strict: true,
    predictableActionArguments: true,
    states: {
        loading: {
            on: {
                LOADED: 'ready',
                ERROR: 'error',
            },
        },
        ready: {
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
                    type: 'final',
                },
            },
        },
        error: {
            type: 'final',
        },
    },
});

const { state, send: sendEvent } = useMachine(stateMachine);

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
});

const email = ref(null);
const setPassword = ref(false);
const password = ref(null);
const role = ref('ROLE_USER');
const firstName = ref(null);
const lastName = ref(null);
const verified = ref(true);
const active = ref(true);

const showForm = computed(() => state.value.matches('ready') && !state.value.done);
const allowSave = computed(() => {
    if (!showForm.value) {
        return false;
    }

    return state.value.matches('ready.ready');
});

const { onResult, onError } = useQuery(GetUserQuery, { userId: props.userId });
onResult(({ data: { User }}) => {
    email.value = User.email;
    role.value = User.roles[0];
    firstName.value = User.firstName;
    lastName.value = User.lastName;
    verified.value = User.verified;
    active.value = User.active;

    sendEvent('LOADED');
});
onError(() => {
    sendEvent('ERROR');
});

const v$ = useVuelidate({
    ...cloneDeep(userValidations),
    password: {
        ...cloneDeep(userValidations.password),
        required: requiredIf(setPassword),
    },
}, { email, password, firstName, lastName, role });

const setEmailDebounce = debounce(function (email) {
    setEmail(email);
}, 100, { leading: true });
function setEmail (value) {
    email.value = value;
}

async function submit () {
    if (!allowSave.value) {
        return;
    }

    sendEvent('SAVE');

    if (!await v$.value.$validate()) {
        sendEvent('ERROR');
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendUserUpdate } = useMutation(AdminUserUpdateMutation);
        await sendUserUpdate({
            user: {
                userId: props.userId,
                email: email.value,
                setPassword: setPassword.value,
                password: password.value,
                role: role.value,
                firstName: firstName.value,
                lastName: lastName.value,
            },
        });

        sendEvent('SAVED');

        setTimeout(() => {
            router.push({ name: 'admin-user' });
        }, 1500);

    } catch (e) {
        logError(e);
        alert('There was a problem saving. Please try again later.');

        sendEvent('ERROR');
        window.scrollTo(0, 0);
    }
}
</script>
