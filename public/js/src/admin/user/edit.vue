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

            <FieldEmail :model-value="user.email"
                        :v="v$.user.email"
                        autocomplete="off"
                        autofocus
                        @update:modelValue="setEmailDebounce" />

            <FieldPassword v-model="user.password"
                           :v="v$.user.password"
                           checkbox-label="Change password"
                           @set-password="user.setPassword = $event" />

            <FieldInput v-model.trim="user.firstName" :v="v$.user.firstName">First name</FieldInput>
            <FieldInput v-model.trim="user.lastName" :v="v$.user.lastName">Last name</FieldInput>

            <FieldRole v-model="user.role" :v="v$.user.role" />

            <AdminButton :edited="edited"
                         :saving="state.matches('ready.saving')"
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
import { computed, ref, watch } from 'vue';
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

const user = ref({
    email: null,
    setPassword: false,
    password: null,
    role: 'ROLE_USER',
    firstName: null,
    lastName: null,
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
    user.value.email = User.email;
    user.value.role = User.roles[0];
    user.value.firstName = User.firstName;
    user.value.lastName = User.lastName;
    verified.value = User.verified;
    active.value = User.active;

    sendEvent('LOADED');
});
onError(() => {
    sendEvent('ERROR');
});

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
                ...user.value,
                userId: props.userId,
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
