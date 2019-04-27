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

        <form v-else-if="showForm" @submit.prevent="submit">
            <form-error v-if="hasValidationErrors" />

            <field-email v-model="email"
                         :server-validation-errors="serverValidationErrors.email"
                         :v="$v.email" />

            <field-password v-model="password"
                            :server-validation-errors="serverValidationErrors.password"
                            checkbox-label="Change Password"
                            @set-password="changePassword = $event" />

            <field-name v-model="firstName"
                        :server-validation-errors="serverValidationErrors.firstName"
                        :v="$v.firstName"
                        label="First Name" />
            <field-name v-model="lastName"
                        :server-validation-errors="serverValidationErrors.lastName"
                        :v="$v.firstName"
                        label="Last Name" />

            <field-role v-model="role"
                        :server-validation-errors="serverValidationErrors.role"
                        :v="$v.role" />

            <div>
                <button type="submit" class="button">Update User</button>
                <router-link :to="{ name: 'admin-user' }"
                             class="form-action">Cancel</router-link>

                <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
            </div>

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
import { logError, hasGraphQlValidationError } from '@/common/lib';

import userValidations from './user.validation';

import fieldEmail from './component/email';
import fieldPassword from './component/password';
import fieldName from './component/name';
import fieldRole from './component/role';

import { GetUserQuery } from '../queries/user.query';
import {
    AdminUserUpdateMutation,
    AdminUserActivateMutation,
    AdminUserVerifyMutation,
    AdminUserSendResetMutation
} from '../queries/admin/user.mutation';

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
            hasLocalValidationErrors: false,
            serverValidationErrors: {},

            email: null,
            changePassword: false,
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
        hasValidationErrors () {
            if (this.hasLocalValidationErrors) {
                return true;
            }

            return Object.keys(this.serverValidationErrors).length > 0;
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
            update (data) {
                this.email = data.User.email;
                this.role = data.User.roles[0];
                this.firstName = data.User.firstName;
                this.lastName = data.User.lastName;
                this.verified = data.User.verified;
                this.active = data.User.active;
            },
            error (e) {
                logError(e);
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

    validations () {
        return cloneDeep(userValidations);
    },

    methods: {
        async submit () {
            if (!this.allowSave) {
                return;
            }

            // get validation to be checked and errors displayed
            this.hasLocalValidationErrors = false;

            this.$v.$touch();

            if (this.$v.$anyError) {
                this.hasLocalValidationErrors = true;
                window.scrollTo(0, 0);

                return;
            }

            this.status = statuses.SAVING;

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserUpdateMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                            email: this.email,
                            changePassword: this.changePassword,
                            password: this.password,
                            role: this.role,
                            firstName: this.firstName,
                            lastName: this.lastName,
                        },
                    },
                });

                this.status = statuses.SAVED;
                this.serverValidationErrors = {};

                setTimeout(() => {
                    this.$router.push({ name: 'admin-user' });
                }, 1500);

            } catch (e) {
                if (hasGraphQlValidationError(e)) {
                    this.serverValidationErrors = e.graphQLErrors[0].validation.user;
                } else {
                    logError(e);
                    alert('There was a problem saving. Please try again later.');
                }

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
                logError(e);
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
                logError(e);
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
                logError(e);
                alert('There was a problem sending the reset. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
