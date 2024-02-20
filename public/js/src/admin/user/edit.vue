<template>
    <div class="form-wrap">
        <Teleport to="#header-actions">
            <div class="header-secondary_actions">
                <RouterLink :to="{ name: 'admin-user-view', params: { userId: props.userId } }">
                    Return to user
                </RouterLink>
            </div>
        </Teleport>

        <h2 class="mt-0">Edit User</h2>

        <LoadingSpinner v-if="state.matches('loading')">
            Loading user…
        </LoadingSpinner>
        <div v-else-if="state.matches('not_found')" class="italic text-center">
            <p>Unable to find the user. Please try again later.</p>
            <p><RouterLink :to="{ name: 'admin-user' }">Return to user list</RouterLink></p>
        </div>
        <div v-else-if="state.matches('error')" class="italic text-center">
            <p>There was a problem loading the user. Please try again later.</p>
            <p><RouterLink :to="{ name: 'admin-user' }">Return to user list</RouterLink></p>
        </div>

        <form v-else-if="showForm" method="post" @submit.prevent="submit" novalidate>
            <FormError v-if="v$.$error && v$.$invalid" />

            <FieldEmail :model-value="user.email"
                        :v="v$.user.email"
                        autocomplete="off"
                        autofocus
                        @update:model-value="setEmailDebounce" />

            <FieldPassword v-model="user.password"
                           :v="v$.user.password"
                           checkbox-label="Change password"
                           autocomplete="off"
                           @set-password="user.setPassword = $event" />

            <FieldInput v-model.trim="user.firstName" :v="v$.user.firstName">First name</FieldInput>
            <FieldInput v-model.trim="user.lastName" :v="v$.user.lastName">Last name</FieldInput>

            <FieldRole v-model="user.role" :v="v$.user.role" />

            <FieldInput v-model="user.phoneNumber" type="tel" :v="v$.user.phoneNumber">Phone number</FieldInput>

            <FormButton :edited="edited"
                        :saving="state.matches('ready.saving')"
                        :saved="state.matches('ready.saved')"
                        :cancel-to="{ name: 'admin-user-view', params: { userId: props.userId } }">
                Update User
            </FormButton>

            <ul class="form-extra_actions">
                <li>
                    <ActivateVerify :user-id="userId"
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
                <li>
                    <AdminDelete record-desc="user" :disabled="!allowSave" @delete="deleteUser" />
                </li>
            </ul>
        </form>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { edit as stateMachineConfig } from '@/common/state_machines';
import { useVuelidate } from '@vuelidate/core';
import { useMutation, useQuery } from '@vue/apollo-composable';
import debounce from 'lodash/debounce';
import { formatPhone, logError } from '@/common/lib';
import FieldEmail from '@/common/field_email';
import FieldPassword from './component/field_password.vue';
import FieldInput from '@/common/field_input';
import FieldRole from './component/field_role.vue';
import ActivateVerify from './component/activate_verify';
import SendActivation from './component/send_activation';
import SendReset from './component/send_reset';
import { GetUserQuery } from '../queries/user.query.graphql';
import {
    AdminUserUpdateMutation,
    AdminUserDeleteMutation,
} from '../queries/user.mutation.graphql';
import userValidation from './user.validation';
import { requiredIf } from '@vuelidate/validators';
import { pick } from 'lodash';

const router = useRouter();

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
});

const user = ref({
    email: null,
    setPassword: false,
    password: null,
    role: 'ROLE_USER',
    firstName: null,
    lastName: null,
    phoneNumber: null,
});
const verified = ref(true);
const active = ref(true);
const edited = ref(false);

const showForm = computed(() => state.value.matches('ready') && !state.value.done);
const allowSave = computed(() => {
    if (!showForm.value) {
        return false;
    }

    return state.value.matches('ready.ready');
});

const { onResult, onError } = useQuery(GetUserQuery, { userId: props.userId });
onResult(({ data: { User }}) => {
    if (!User) {
        sendEvent({ type: 'NOT_FOUND' });

        return;
    }

    user.value.email = User.email;
    user.value.role = User.roles[0];
    user.value.firstName = User.firstName;
    user.value.lastName = User.lastName;

    verified.value = User.verified;
    active.value = User.active;

    if (User.userData) {
        user.value.phoneNumber = formatPhone(User.userData.phoneNumber);
    }

    sendEvent({ type: 'LOADED' });
});
onError(() => {
    sendEvent({ type: 'ERROR' });
});

const userValidations = userValidation();
const v$ = useVuelidate({
    user: {
        ...userValidations,
        password: {
            ...userValidations.password,
            required: requiredIf(user.value.setPassword),
        },
    },
}, { user });

watch(
    user,
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
    if (!allowSave.value) {
        return;
    }

    sendEvent({ type: 'SAVE' });

    if (!await v$.value.$validate()) {
        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendUserUpdate } = useMutation(AdminUserUpdateMutation);
        await sendUserUpdate({
            user: {
                ...pick(user.value, ['email', 'setPassword', 'password', 'role', 'firstName', 'lastName']),
                userId: props.userId,
                userData: {
                    phoneNumber: user.value.phoneNumber,
                },
            },
        });

        sendEvent({ type: 'SAVED' });

        setTimeout(() => {
            router.push({ name: 'admin-user-view', params: { userId: props.userId } });
        }, 500);

    } catch (e) {
        logError(e);
        alert('There was a problem saving. Please try again later.');

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }
}

const deleteUser = async () => {
    if (!allowSave.value) {
        return;
    }

    sendEvent({ type: 'DELETE' });

    try {
        const { mutate: sendUserDelete } = useMutation(AdminUserDeleteMutation);
        await sendUserDelete({
            userId: props.userId,
        });

        edited.value = false;
        sendEvent({ type: 'DELETED' });

        setTimeout(() => {
            router.push({ name: 'admin-user' });
        }, 1500);

    } catch (e) {
        logError(e);
        alert('There was a problem deleting the user. Please try again later.');

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }
};
</script>
