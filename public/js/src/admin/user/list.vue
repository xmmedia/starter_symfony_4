<template>
    <div>
        <portal to="header-actions">
            <router-link :to="{ name: 'admin-user-add' }"
                         class="header-action header-action-main">Add User</router-link>
            <div class="header-secondary_actions">
                <button type="button" class="button-link" @click="refresh">Refresh</button>
            </div>
        </portal>

        <loading-spinner v-if="state.matches('loading')">
            Loading users…
        </loading-spinner>
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
                        <div class="record_list-col">Customer</div>
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
                            <span v-if="user.userId === $store.state.user.userId" class="pl-3 italic">
                                You
                            </span>
                        </div>
                        <div class="record_list-col">{{ user.name }}</div>
                        <div class="record_list-col">
                            <template v-if="user.customer">
                                {{ user.customer.customerNumber }} –
                                {{ user.customer.name }}
                            </template>
                            <i v-else>None</i>
                        </div>
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
        </template>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import { mapState } from 'vuex';
import stateMixin from '@/common/state_mixin';
import { GetUsersQuery } from '../queries/user.query.graphql';

const stateMachine = Machine({
    id: 'component',
    initial: 'loading',
    strict: true,
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

    mixins: [
        stateMixin,
    ],

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,
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
            update ({ Users }) {
                this.stateEvent('LOADED');

                return Users;
            },
            error () {
                this.stateEvent('ERROR');
            },
        },
    },

    methods: {
        refresh () {
            this.stateEvent('REFRESH');
            this.$apollo.queries.users.refetch();
        },
    },
}
</script>
