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

            <FieldEmail :model-value="user.email"
                        :v="v$.user.email"
                        autocomplete="off"
                        autofocus
                        @update:modelValue="setEmailDebounce" />

            <FieldPassword v-model="user.password"
                           :v="v$.user.password"
                           :user-data="userDataForPassword"
                           checkbox-label="Set password"
                           @set-password="user.setPassword = $event" />

            <div class="field-wrap field-wrap-checkbox">
                <input id="inputActive" v-model="user.active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <FieldInput v-model.trim="user.firstName" :v="v$.user.firstName">First name</FieldInput>
            <FieldInput v-model.trim="user.lastName" :v="v$.user.lastName">Last name</FieldInput>

            <FieldRole v-model="user.role" :v="v$.user.role" />

            <div v-if="!user.setPassword && user.active" class="field-wrap">
                <div class="field-wrap field-wrap-checkbox">
                    <input id="inputSendInvite" v-model="user.sendInvite" type="checkbox">
                    <label for="inputSendInvite">Send invite</label>
                </div>
                <div class="field-help">
                    The user will need to follow the link in the invite email
                    before their account will be fully activated.
                </div>
            </div>

            <AdminButton :edited="edited"
                         :saving="state.matches('submitting')"
                         :saved="state.matches('saved')"
                         :cancel-to="{ name: 'admin-user' }">
                Add User
            </AdminButton>
        </form>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
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
import FieldPassword from './component/field_password.vue';
import FieldInput from '@/common/field_input';
import FieldRole from './component/field_role.vue';
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

const user = ref({
    email: null,
    setPassword: false,
    password: null,
    role: 'ROLE_USER',
    active: true,
    firstName: null,
    lastName: null,
    sendInvite: true,
});
const edited = ref(false);

const userDataForPassword = computed(() => [
    user.value.email,
    user.value.firstName,
    user.value.lastName,
]);

const v$ = useVuelidate({
    user: {
        ...cloneDeep(userValidations),
        password: {
            ...cloneDeep(userValidations.password),
            required: requiredIf(user.value.setPassword),
        },
    },
}, { user });

watch(user,
    () => {
        if (state.value.matches('ready')) {
            edited.value = true;
        }
    },
    { deep: true, flush: 'sync' },
);

const setEmailDebounce = debounce(function (email) {
    setEmail(email);
}, 100, { leading: true });
function setEmail (value) {
    user.value.email = value;
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
                ...user.value,
                userId: uuid4(),
                sendInvite: user.value.setPassword || !user.value.active ? false : user.value.sendInvite,
                userData: {
                    phoneNumber: user.value.phoneNumber,
                },
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
