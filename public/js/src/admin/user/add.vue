<template>
    <div class="form-wrap">
        <Teleport to="#header-page-title"><span>Users</span></Teleport>
        <Teleport to="#header-actions">
            <div class="header-secondary_actions">
                <RouterLink :to="{ name: 'admin-user' }">Return to list</RouterLink>
            </div>
        </Teleport>

        <h2 class="mt-0">Add User</h2>
        <form method="post" novalidate @submit.prevent="submit">
            <FormError v-if="v$.$error && v$.$invalid" />

            <FormFields v-model="user" :v="v$.user" />

            <FormButton :edited="edited"
                        :saving="state.matches('submitting')"
                        :saved="state.matches('saved')"
                        :cancel-to="{ name: 'admin-user' }">
                Add User
            </FormButton>
        </form>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { add as stateMachineConfig } from '@/common/state_machines';
import { useMutation } from '@vue/apollo-composable';
import { v4 as uuid4 } from 'uuid';
import { logError } from '@/common/lib';
import { AdminUserAddMutation } from '../queries/user.mutation.graphql';
import { pick } from 'lodash';
import { useForm } from './form';
import FormFields from './component/form_fields.vue';

const router = useRouter();

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const {
    user,
    edited,
    v$,
} = useForm(state);

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
        const userId = uuid4();

        const { mutate: sendUserAdd } = useMutation(AdminUserAddMutation);
        await sendUserAdd({
            user: {
                ...pick(user.value, ['email', 'setPassword', 'password', 'role', 'active', 'firstName', 'lastName']),
                userId,
                sendInvite: user.value.setPassword || !user.value.active ? false : user.value.sendInvite,
                userData: {
                    phoneNumber: user.value.phoneNumber,
                },
            },
        });

        sendEvent({ type: 'SUBMITTED' });

        setTimeout(() => {
            router.push({ name: 'admin-user-view', params: { userId } });
        }, 500);

    } catch (e) {
        logError(e);
        alert('There was a problem saving. Please try again later.');

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }
}
</script>
