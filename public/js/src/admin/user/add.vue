<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-user' }">Return to List</router-link>
            </div>
        </portal>

        <h2 class="mt-0">Add User</h2>
        <form method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <field-email v-model="email" :v="$v.email" />

            <field-password v-model="password"
                            :v="$v.password"
                            checkbox-label="Set Password"
                            @set-password="setPassword = $event" />

            <div class="field-wrap-checkbox">
                <input id="inputActive" v-model="active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <field-name v-model="firstName" :v="$v.firstName" label="First Name" />
            <field-name v-model="lastName" :v="$v.lastName" label="Last Name" />

            <field-role v-model="role" :v="$v.role" />

            <div v-if="!setPassword && active" class="field-wrap-checkbox">
                <input id="inputSendInvite" v-model="sendInvite" type="checkbox">
                <label for="inputSendInvite">Send Invite</label>
                <div class="field-help">
                    The user will need to follow the link in the invite email
                    before their account will be fully activated.
                </div>
            </div>

            <admin-button :status="status" :cancel-to="{ name: 'admin-user' }">
                Add User
            </admin-button>
        </form>
    </div>
</template>

<script>
import uuid4 from 'uuid/v4';
import cloneDeep from 'lodash/cloneDeep';
import { waitForValidation } from '@/common/lib';

import userValidations from './user.validation';

import fieldEmail from './component/email';
import fieldPassword from './component/password';
import fieldName from './component/name';
import fieldRole from './component/role';

import { AdminUserAddMutation } from '../queries/admin/user.mutation.graphql';

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

                setTimeout(() => {
                    this.$router.push({ name: 'admin-user' });
                }, 1500);

            } catch (e) {
                alert('There was a problem saving. Please try again later.');

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
