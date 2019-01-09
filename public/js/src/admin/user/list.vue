<template>
    <div>
        <div v-if="status === 'loading'" class="italic">Loading users...</div>
        <div v-else-if="status === 'error'">There was a problem loading the user list. Please try again later.</div>

        <ul v-else-if="status === 'loaded'" class="record_list-wrap">
            <li class="record_list-headers">
                <div class="record_list-col">Username</div>
                <div class="record_list-col">Name</div>
                <div class="record_list-col">Account Status</div>
                <div class="record_list-col">Last Login (Count)</div>
                <div class="record_list-col">Role</div>
                <div class="record_list-col"></div>
            </li>

            <li v-for="user in users"
                :key="user.id"
                :class="{ 'record_list-item-inactive' : (!user.active || !user.verified) }"
                class="record_list-item">
                <div class="record_list-col">{{ user.email }}</div>
                <div class="record_list-col">{{ user.name }}</div>
                <div class="record_list-col">{{ user|accountStatus }}</div>
                <div class="record_list-col user_list-last_login">
                    <template v-if="user.loginCount > 0">
                        <local-time :datetime="user.lastLogin" /> ({{ user.loginCount }})
                    </template>
                    <i v-else>Never logged in</i>
                </div>
                <div class="record_list-col">{{ availableRoles[user.roles[0]] }}</div>

                <div class="record_list-col record_list-col-actions">
                    <router-link :to="{ name: 'admin-user-edit', params: { userId: user.id } }"
                                 class="record_list-action">
                        Edit
                    </router-link>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import gql from 'graphql-tag';
import { mapState } from 'vuex';
import { logError } from '@/common/lib';

const statuses = {
    LOADING: 'loading',
    ERROR: 'error',
    LOADED: 'loaded',
};

export default {
    components: {},

    filters: {
        accountStatus (user) {
            if (!user.active) {
                return 'Inactive';
            } else if (!user.verified) {
                return 'Not Verified';
            }

            return 'Active';
        },
    },

    props: {},

    data () {
        return {
            status: statuses.LOADING,
            users: null,
        };
    },

    computed: {
        ...mapState([
            'availableRoles',
        ]),
    },

    apollo: {
        users: {
            query: gql`query {
              Users {
                id
                email
                name
                lastLogin
                loginCount
                roles
                verified
                active
              }
            }`,
            update: data => data.Users,
            error (e) {
                logError(e);
                this.status = statuses.ERROR;
            },
            watchLoading (isLoading) {
                if (!isLoading && this.status !== statuses.ERROR) {
                    this.status = statuses.LOADED;
                }
            },
        },
    },
}
</script>
