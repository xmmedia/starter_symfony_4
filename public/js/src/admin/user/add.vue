<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-user' }">Return to List</router-link>
            </div>
        </portal>

        <h2 class="mt-0">Add User</h2>
        <form @submit.prevent="submit">
            <form-error v-if="hasValidationErrors" />

            <field-email v-model="email"
                         :server-validation-errors="serverValidationErrors.email"
                         :v="$v.email" />

            <field-password v-model="password"
                            :server-validation-errors="serverValidationErrors.password"
                            checkbox-label="Set Password"
                            @set-password="setPassword = $event" />

            <div class="field-wrap-checkbox">
                <field-errors :errors="serverValidationErrors" field="active" />
                <input id="inputActive" v-model="active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <field-name v-model="firstName"
                        :server-validation-errors="serverValidationErrors.firstName"
                        :v="$v.firstName"
                        label="First Name" />
            <field-name v-model="lastName"
                        :server-validation-errors="serverValidationErrors.lastName"
                        :v="$v.lastName"
                        label="Last Name" />

            <field-role v-model="role"
                        :server-validation-errors="serverValidationErrors"
                        :v="$v.role" />

            <div v-if="!setPassword && active" class="field-wrap-checkbox">
                <field-errors :errors="serverValidationErrors" field="sendInvite" />
                <input id="inputSendInvite" v-model="sendInvite" type="checkbox">
                <label for="inputSendInvite">Send Invite</label>
                <div class="field-help">
                    The user will need to follow the link in the invite email
                    before their account will be fully activated.
                </div>
            </div>

            <div>
                <button type="submit" class="button">Add User</button>
                <router-link :to="{ name: 'admin-user' }"
                             class="form-action">Cancel</router-link>

                <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
            </div>
        </form>
    </div>
</template>

<script>
import uuid4 from 'uuid/v4';
import cloneDeep from 'lodash/cloneDeep';
import { logError, hasGraphQlValidationError } from '@/common/lib';

import userValidations from './user.validation';

import fieldEmail from './component/email';
import fieldPassword from './component/password';
import fieldName from './component/name';
import fieldRole from './component/role';

import { AdminUserAddMutation } from '../queries/admin/user.mutation';

const statuses = {
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

    data () {
        return {
            status: statuses.LOADED,
            hasLocalValidationErrors: false,
            serverValidationErrors: {},

            email: null,
            setPassword: false,
            password: null,
            role: 'ROLE_USER',
            active: true,
            firstName: null,
            lastName: null,
            sendInvite: true,
        };
    },

    computed: {
        allowSave () {
            return [statuses.LOADED, statuses.SAVED].includes(this.status);
        },
        hasValidationErrors () {
            if (this.hasLocalValidationErrors) {
                return true;
            }

            return Object.keys(this.serverValidationErrors).length > 0;
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
                    mutation: AdminUserAddMutation,
                    variables: {
                        user: {
                            userId: uuid4(),
                            email: this.email,
                            setPassword: this.setPassword,
                            password: this.password,
                            role: this.role,
                            active: this.active,
                            firstName: this.firstName,
                            lastName: this.lastName,
                            sendInvite: this.setPassword || !this.active ? false : this.sendInvite,
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
    },
}
</script>
