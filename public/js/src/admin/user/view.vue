<template>
    <Portal to="header-actions">
        <div class="header-secondary_actions">
            <RouterLink :to="{ name: 'admin-user' }">Return to list</RouterLink>
            <RouterLink v-if="state.matches('loaded')"
                        :to="{ name: 'admin-user-edit', params: { userId: props.userId } }"
                        class="header-action header-action-main">Edit User</RouterLink>
        </div>
    </Portal>

    <LoadingSpinner v-if="state.matches('loading')">
        Loading user…
    </LoadingSpinner>
    <div v-else-if="state.matches('not_found')" class="italic text-center">
        <p>The user could not be found.</p>
        <p><RouterLink :to="{ name: 'admin-user' }">Return to list</RouterLink></p>
    </div>
    <div v-else-if="state.matches('error')" class="italic text-center">
        <p>There was a problem loading the user. Please try again later.</p>
        <p><RouterLink :to="{ name: 'admin-user' }">Return to list</RouterLink></p>
    </div>

    <template v-if="state.matches('loaded')">
        <Portal to="header-page-title">{{ user.name }}</Portal>

        <div class="record_view-item_wrap">
            <div class="record_view-item">
                <div class="record_view-item_label">Name</div>
                <div class="record_view-item_value">{{ user.name }}</div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">
                    Email
                    <Copy :text="user.email" />
                </div>
                <div class="record_view-item_value">
                    <a :href="'mailto:'+user.email">{{ user.email }}</a>
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">
                    Phone Number
                    <Copy v-if="user.userData?.phoneNumber" :text="formatPhone(user.userData.phoneNumber)" />
                </div>
                <div class="record_view-item_value">
                    <ViewPhone v-if="user.userData?.phoneNumber" :phone-number="user.userData.phoneNumber" />
                    <template v-else>–</template>
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Account Status</div>
                <div class="record_view-item_value"><AccountStatus :user="user" /></div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Email Verified</div>
                <div class="record_view-item_value">
                    <AdminIcon icon="check"
                               :class="{ 'text-green-500' : user.verified }"
                               class="record_list-icon text-gray-500" />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Active</div>
                <div class="record_view-item_value">
                    <AdminIcon icon="check"
                               :class="{ 'text-green-500' : user.active }"
                               class="record_list-icon text-gray-500" />
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">Last Login</div>
                <div class="record_view-item_value">
                    <LocalTime v-if="user.loginCount > 0 && user.lastLogin" :datetime="user.lastLogin" />
                    <i v-else>Never logged in</i>
                </div>
            </div>
            <div v-if="user.loginCount > 0" class="record_view-item">
                <div class="record_view-item_label">Login Count</div>
                <div class="record_view-item_value">
                    {{ user.loginCount }}
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">System Role</div>
                <div class="record_view-item_value">
                    {{ rootStore.availableRoles[user.roles[0]] }}
                </div>
            </div>
            <div class="record_view-item">
                <div class="record_view-item_label">
                    Request Login Link
                    <Copy :text="requestLoginLink" />
                </div>
                <div class="record_view-item_value">{{ requestLoginLink }}</div>
            </div>
        </div>
    </template>
</template>

<script setup>
import { ref } from 'vue';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { useRootStore } from '@/admin/stores/root';
import { view as stateMachineConfig } from "@/common/state_machines";
import { formatPhone } from '@/common/lib';
import { useQuery } from '@vue/apollo-composable';
import { GetUserViewQuery } from '../queries/user.query.graphql';
import { useHead } from '@unhead/vue';
import Copy from '@/common/copy.vue';
import ViewPhone from '@/common/view_phone.vue';
import AccountStatus from './component/account_status.vue';

const rootStore = useRootStore();

const stateMachine = createMachine(stateMachineConfig);
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const props = defineProps({
    userId: {
        type: String,
        required: true,
    },
});

const user = ref(null);
const requestLoginLink = ref(null);

useHead({
    title: () => (state.value.matches('loaded') ? user.value.name + ' | ' : '') + 'Users | Admin',
});

const { onResult, onError } = useQuery(GetUserViewQuery, { userId: props.userId });
onResult(({ data: { User }}) => {
    if (!User) {
        sendEvent({ type: 'NOT_FOUND' });

        return;
    }

    user.value = User;
    requestLoginLink.value = `${window.location.origin}/login?magic=1&email=${encodeURIComponent(user.value.email)}`;

    sendEvent({ type: 'LOADED' });
});
onError(() => {
    sendEvent({ type: 'ERROR' });
});
</script>
