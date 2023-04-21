<template>
    <div>
        <Portal to="header-actions">
            <RouterLink :to="{ name: 'admin-user-add' }"
                        class="header-action header-action-main">Add User</RouterLink>
            <div class="header-secondary_actions">
                <button type="button" class="button-link" @click="refresh">Refresh</button>
            </div>
        </Portal>

        <LoadingSpinner v-if="state.matches('loading')">
            Loading users…
        </LoadingSpinner>
        <div v-if="state.matches('error')" class="italic text-center">
            There was a problem loading the user list. Please try again later.
        </div>

        <template v-if="state.matches('loaded')">
            <div v-if="users.length === 0" class="italic text-center">
                No users were found.
            </div>

            <template v-else>
                <div class="record_list-record_count">Showing {{ users.length }}</div>

                <ul class="record_list-wrap">
                    <li class="record_list-headers">
                        <div class="record_list-col">Username</div>
                        <div class="record_list-col">Name</div>
                        <div class="record_list-col">Account Status</div>
                        <div class="record_list-col">Last Login (Count)</div>
                        <div class="record_list-col">Role</div>
                        <div class="record_list-col"></div>
                    </li>

                    <li v-for="user in users"
                        :key="user.userId"
                        :class="{ 'record_list-item-inactive' : (!user.active || !user.verified) }"
                        class="record_list-item">
                        <div class="record_list-col">
                            {{ user.email }}
                            <span v-if="user.userId === rootStore.user.userId" class="pl-3 italic">
                                You
                            </span>
                        </div>
                        <div class="record_list-col">{{ user.name }}</div>
                        <div class="record_list-col">{{ accountStatus(user) }}</div>
                        <div class="record_list-col user_list-last_login">
                            <template v-if="user.loginCount > 0">
                                <LocalTime v-if="user.lastLogin" :datetime="user.lastLogin" />
                                ({{ user.loginCount }})
                            </template>
                            <i v-else>Never logged in</i>
                        </div>
                        <div class="record_list-col">{{ rootStore.availableRoles[user.roles[0]] }}</div>

                        <div class="record_list-col record_list-col-actions">
                            <RouterLink :to="{ name: 'admin-user-edit', params: { userId: user.userId } }">
                                Edit
                            </RouterLink>
                        </div>
                    </li>
                </ul>
            </template>
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { useRootStore } from '@/admin/stores/root';
import { useQuery } from '@vue/apollo-composable';
import { GetUsersQuery } from '../queries/user.query.graphql';

const rootStore = useRootStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'loading',
    strict: true,
    predictableActionArguments: true,
    states: {
        loading: {
            on: {
                LOADED: 'loaded',
                ERROR: 'error',
            },
        },
        loaded: {
            on: {
                REFRESH: 'loading',
            },
        },
        error: {
            on: {
                REFRESH: 'loading',
            },
        },
    },
});

const { state, send: sendEvent } = useMachine(stateMachine);

const users = ref();

const { onResult, onError, refetch: usersRefetch } = useQuery(GetUsersQuery);
onResult(({ data: { Users }}) => {
    users.value = Users;
    sendEvent('LOADED');
});
onError(() => {
    sendEvent('ERROR');
});

function refresh () {
    sendEvent('REFRESH');
    usersRefetch();
}

function accountStatus (user) {
    if (!user.active) {
        return 'Inactive';
    } else if (!user.verified) {
        return 'Not Verified';
    }

    return 'Active';
}
</script>
