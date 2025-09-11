<template>
    <div class="form-wrap">
        <Teleport to="#header-page-title"><span>Users</span></Teleport>
        <Teleport to="#header-actions">
            <div class="header-secondary_actions">
                <RouterLink :to="{ name: 'admin-user-view', params: { userId } }">
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

        <form v-else-if="showForm" method="post" novalidate @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />

            <FormFields v-model="user" :editing="true" :v="v$.user" />

            <FormButton :edited="edited"
                        :saving="state.matches('ready.saving')"
                        :saved="state.matches('ready.saved')"
                        :cancel-to="{ name: 'admin-user-view', params: { userId } }">
                Update User
            </FormButton>
        </form>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { edit as stateMachineConfig } from '@/common/state_machines';
import { useMutation, useQuery } from '@vue/apollo-composable';
import { formatPhone, logError } from '@/common/lib';
import { GetUserQuery } from '../queries/user.query.graphql';
import { AdminUserUpdateMutation } from '../queries/user.mutation.graphql';
import { pick } from 'lodash';
import { useForm } from './form';
import FormFields from './component/form_fields.vue';

const router = useRouter();

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
});

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const {
    user,
    edited,
    v$,
} = useForm(state);

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

const submit = async () => {
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
</script>
