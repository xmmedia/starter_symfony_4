<template>
    <div>
        <portal to="header-actions">
            <router-link :to="{ name: 'admin-user-add' }"
                         class="header-action header-action-main">Add User</router-link>
        </portal>

        <loading-spinner v-if="status === 'loading'">
            Loading users...
        </loading-spinner>
        <div v-else-if="status === 'error'" class="italic text-center">
            There was a problem loading the user list. Please try again later.
        </div>

        <div v-else-if="users && users.length === 0" class="italic text-center">
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
                        <router-link :to="{ name: 'admin-user-edit', params: { userId: user.userId } }"
                                     class="record_list-action">
                            Edit
                        </router-link>
                    </div>
                </li>
            </ul>
        </template>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import { GetUsersQuery } from '../queries/user.query';

const statuses = {
    LOADING: 'loading',
    ERROR: 'error',
    LOADED: 'loaded',
};

export default {
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
            query: GetUsersQuery,
            update: data => data.Users,
            error () {
                this.status = statuses.ERROR;
            },
            watchLoading (isLoading) {
                if (!isLoading && this.status === statuses.LOADING) {
                    this.status = statuses.LOADED;
                }
            },
            fetchPolicy: 'network-only',
        },
    },
}
</script>
