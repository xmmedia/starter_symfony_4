<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-user' }">Return to List</router-link>
            </div>
        </portal>

        <h2 class="mt-0">Edit User</h2>

        <loading-spinner v-if="status === 'loading'">
            Loading user...
        </loading-spinner>
        <div v-else-if="status === 'error'" class="italic text-center">
            There was a problem loading the user. Please try again later.
        </div>

        <form v-else-if="showForm" method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <field-email v-model="email"
                         :v="$v.email" />

            <field-password v-model="password"
                            :v="$v.password"
                            checkbox-label="Change Password"
                            @set-password="setPassword = $event" />

            <field-name v-model="firstName" :v="$v.firstName" label="First Name" />
            <field-name v-model="lastName" :v="$v.lastName" label="Last Name" />

            <field-role v-model="role" :v="$v.role" />

            <admin-button :status="status" :cancel-to="{ name: 'admin-user' }">
                Update User
            </admin-button>

            <ul class="form-extra_actions">
                <li>
                    <button v-if="verified"
                            class="button-link form-action"
                            type="button"
                            @click="toggleActive"
                            v-html="activeButtonText"></button>
                    <button v-else
                            class="button-link form-action"
                            type="button"
                            @click="verify">Manually Verify User</button>
                </li>
                <li v-if="active">
                    <button class="button-link form-action"
                            type="button"
                            @click="sendReset">Send Password Reset</button>
                </li>
            </ul>
        </form>
    </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { waitForValidation } from '@/common/lib';

import userValidations from './user.validation';

import fieldEmail from './component/email';
import fieldPassword from './component/password';
import fieldName from './component/name';
import fieldRole from './component/role';

import { GetUserQuery } from '../queries/user.query.graphql';
import {
    AdminUserUpdateMutation,
    AdminUserActivateMutation,
    AdminUserVerifyMutation,
    AdminUserSendResetMutation,
} from '../queries/admin/user.mutation.graphql';

const statuses = {
    LOADING: 'loading',
    ERROR: 'error',
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        fieldEmail,
        fieldPassword,
        fieldName,
        fieldRole,
    },

    props: {
        userId: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            status: statuses.LOADING,

            email: null,
            setPassword: false,
            password: null,
            role: 'ROLE_USER',
            firstName: null,
            lastName: null,
            verified: false,
            active: false,
        };
    },

    computed: {
        showForm () {
            return [statuses.LOADED, statuses.SAVING, statuses.SAVED].includes(this.status);
        },
        allowSave () {
            return [statuses.LOADED, statuses.SAVED].includes(this.status);
        },

        activeButtonText () {
            return this.active ? 'Deactivate User' : 'Activate User';
        },
    },

    apollo: {
        user: {
            query: GetUserQuery,
            variables () {
                return {
                    userId: this.userId,
                };
            },
            update ({ User }) {
                this.email = User.email;
                this.role = User.roles[0];
                this.firstName = User.firstName;
                this.lastName = User.lastName;
                this.verified = User.verified;
                this.active = User.active;

                if (this.status === statuses.LOADING) {
                    this.status = statuses.LOADED;
                }
            },
            error () {
                this.status = statuses.ERROR;
            },
        },
    },

    validations () {
        return cloneDeep(userValidations);
    },

    methods: {
        waitForValidation,

        async submit () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            this.$v.$touch();

            if (!await this.waitForValidation()) {
                this.status = statuses.LOADED;
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserUpdateMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                            email: this.email,
                            setPassword: this.setPassword,
                            password: this.password,
                            role: this.role,
                            firstName: this.firstName,
                            lastName: this.lastName,
                        },
                    },
                });

                this.status = statuses.SAVED;

                setTimeout(() => {
                    this.$router.push({ name: 'admin-user' });
                }, 1500);

            } catch (e) {
                alert('There was a problem saving. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        async toggleActive () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserActivateMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                            action: this.active ? 'deactivate' : 'activate',
                        },
                    },
                });

                this.active = !this.active;

                this.status = statuses.SAVED;

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 3000);

            } catch (e) {
                alert('There was a problem toggling the active state. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        async verify () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserVerifyMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                        },
                    },
                });

                this.verified = true;
                this.status = statuses.SAVED;

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 3000);

            } catch (e) {
                alert('There was a problem verifying the user. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        async sendReset () {
            if (!this.allowSave) {
                return;
            }

            this.status = statuses.SAVING;

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserSendResetMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                        },
                    },
                });

                this.status = statuses.SAVED;

                setTimeout(() => {
                    this.status = statuses.LOADED;
                }, 3000);

            } catch (e) {
                alert('There was a problem sending the reset. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
