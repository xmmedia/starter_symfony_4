<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-user' }">Return to List</router-link>
            </div>
        </portal>

        <h2 class="mt-0">Add User</h2>
        <form @submit.prevent="submit">
            <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                <li>Please fix the errors below and try again.</li>
            </ul>

            <field-email v-model="email" :validation-errors="validationErrors" />

            <field-password v-model="password"
                            :validation-errors="validationErrors"
                            checkbox-label="Set Password"
                            @set-password="setPassword = $event" />

            <div class="field-wrap-checkbox">
                <field-errors :errors="validationErrors" field="active" />
                <input id="inputActive" v-model="active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <field-name v-model="firstName"
                        :validation-errors="validationErrors"
                        label="First Name"
                        field="firstName" />
            <field-name v-model="lastName"
                        :validation-errors="validationErrors"
                        label="Last Name"
                        field="lastName" />

            <field-role v-model="role" :validation-errors="validationErrors" />

            <div v-if="!setPassword && active" class="field-wrap-checkbox">
                <field-errors :errors="validationErrors" field="sendInvite" />
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
import { logError, hasGraphQlValidationError } from '@/common/lib';
import fieldEmail from './component/email';
import fieldPassword from './component/password';
import fieldName from './component/name';
import fieldRole from './component/role';
import { AdminUserCreateMutation } from '../queries/admin/user.mutation';

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        'field-email': fieldEmail,
        'field-password': fieldPassword,
        'field-name': fieldName,
        'field-role': fieldRole,
    },

    data () {
        return {
            status: statuses.LOADED,
            validationErrors: {},

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
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },
    },

    methods: {
        async submit () {
            this.status = statuses.SAVING;

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserCreateMutation,
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
                this.validationErrors = {};

                setTimeout(() => {
                    this.$router.push({ name: 'admin-user' });
                }, 1500);

            } catch (e) {
                if (hasGraphQlValidationError(e)) {
                    this.validationErrors = e.graphQLErrors[0].validation.user;
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
