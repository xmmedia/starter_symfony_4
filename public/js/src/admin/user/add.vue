<template>
    <div class="form-wrap">
        <Portal to="header-actions">
            <div class="header-secondary_actions">
                <RouterLink :to="{ name: 'admin-user' }">Return to list</RouterLink>
            </div>
        </Portal>

        <h2 class="mt-0">Add User</h2>
        <form method="post" @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />

            <FieldEmail :model-value="email"
                        :v="v$.email"
                        autocomplete="off"
                        autofocus
                        @update:modelValue="setEmailDebounce" />

            <FieldPassword v-model="password"
                           :v="v$.password"
                           :user-data="userDataForPassword"
                           checkbox-label="Set password"
                           @set-password="setPassword = $event" />

            <div class="field-wrap field-wrap-checkbox">
                <input id="inputActive" v-model="active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <FieldInput v-model.trim="firstName" :v="v$.firstName">First name</FieldInput>
            <FieldInput v-model.trim="lastName" :v="v$.lastName">Last name</FieldInput>

            <FieldRole v-model="role" :v="v$.role" />

            <div v-if="!setPassword && active" class="field-wrap">
                <div class="field-wrap field-wrap-checkbox">
                    <input id="inputSendInvite" v-model="sendInvite" type="checkbox">
                    <label for="inputSendInvite">Send invite</label>
                </div>
                <div class="field-help">
                    The user will need to follow the link in the invite email
                    before their account will be fully activated.
                </div>
            </div>

            <AdminButton :saving="state.matches('submitting')"
                         :saved="state.matches('saved')"
                         :cancel-to="{ name: 'admin-user' }">
                Add User
            </AdminButton>
        </form>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useVuelidate } from '@vuelidate/core';
import { useMutation } from '@vue/apollo-composable';
import cloneDeep from 'lodash/cloneDeep';
import debounce from 'lodash/debounce';
import { v4 as uuid4 } from 'uuid';
import { logError } from '@/common/lib';
import FieldEmail from '@/common/field_email';
import FieldPassword from './component/password';
import FieldInput from '@/common/field_input';
import FieldRole from './component/role';
import { AdminUserAddMutation } from '../queries/admin/user.mutation.graphql';
import userValidations from './user.validation';
import { requiredIf } from '@vuelidate/validators';

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
                SUBMITTED: 'saved',
                ERROR: 'ready',
            },
        },
        saved: {
            type: 'final',
        },
    },
});

const { state, send: sendEvent } = useMachine(stateMachine);

const email = ref(null);
const setPassword = ref(false);
const password = ref(null);
const role = ref('ROLE_USER');
const active = ref(true);
const firstName = ref(null);
const lastName = ref(null);
const sendInvite = ref(true);

const userDataForPassword = computed(() => [
    email.value,
    firstName.value,
    lastName.value,
]);

const v$ = useVuelidate({
    ...cloneDeep(userValidations),
    password: {
        ...cloneDeep(userValidations.password),
        required: requiredIf(setPassword.value),
    },
}, { email, password, firstName, lastName, role });

const setEmailDebounce = debounce(function (email) {
    setEmail(email);
}, 100, { leading: true });
function setEmail (value) {
    email.value = value;
}

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
        const { mutate: sendUserAdd } = useMutation(AdminUserAddMutation);
        await sendUserAdd({
            user: {
                userId: uuid4(),
                email: email.value,
                setPassword: setPassword.value,
                password: password.value,
                role: role.value,
                active: active.value,
                firstName: firstName.value,
                lastName: lastName.value,
                sendInvite: setPassword.value || !active.value ? false : sendInvite.value,
            },
        });

        sendEvent('SUBMITTED');

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
