<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <form class="p-4" method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />
            <div v-if="status === 'saved'" class="alert alert-success mb-4" role="alert">
                <div>
                    Your password has been updated.<br>
                    You will need to login again.
                </div>
                <a :href="loginUrl" class="pl-4">Go to Login</a>
            </div>

            <!-- this is for the browser so it can generate a new password -->
            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       :value="$store.state.user.email"
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <field-password v-model="currentPassword"
                            :v="$v.currentPassword"
                            autocomplete="current-password">
                Current Password
                <template #required-msg>Your current password is required.</template>
            </field-password>

            <field-password v-model="newPassword"
                            :v="$v.newPassword"
                            :show-help="true"
                            autocomplete="new-password">
                New Password
                <template #required-msg>A new password is required.</template>
            </field-password>
            <field-password v-model="repeatPassword"
                            :v="$v.repeatPassword"
                            autocomplete="new-password">
                New Password Again
                <template #required-msg>Re-enter your new password.</template>
            </field-password>

            <div class="mb-4 text-sm">After changing your password, you will need to login again.</div>

            <admin-button :status="status"
                          :cancel-to="{ name: 'user-profile-edit' }">
                Change Password
            </admin-button>
        </form>
    </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { waitForValidation } from '@/common/lib';
import profileTabs from './component/tabs';
import fieldPassword from './component/password';
import { ChangePassword } from '../queries/user.mutation.graphql';

import userValidations from './user.validation';

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        profileTabs,
        fieldPassword,
    },

    data () {
        return {
            status: statuses.LOADED,

            currentPassword: null,
            newPassword: null,
            repeatPassword: null,
        };
    },

    computed: {
        loginUrl () {
            return this.$router.resolve({ name: 'login' }).href;
        },
    },

    validations () {
        return {
            currentPassword: cloneDeep(userValidations.currentPassword),
            newPassword: cloneDeep(userValidations.newPassword),
            repeatPassword: cloneDeep(userValidations.repeatPassword),
        };
    },

    methods: {
        waitForValidation,

        async submit () {
            this.status = statuses.SAVING;

            this.$v.$touch();

            if (!await this.waitForValidation()) {
                this.status = statuses.LOADED;
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: ChangePassword,
                    variables: {
                        user: {
                            currentPassword: this.currentPassword,
                            newPassword: this.newPassword,
                            repeatPassword: this.repeatPassword,
                        },
                    },
                });

                this.currentPassword = null;
                this.newPassword = null;
                this.repeatPassword = null;
                this.$v.$reset();

                this.status = statuses.SAVED;

                setTimeout(() => {
                    window.location = this.loginUrl;
                }, 30000);

            } catch (e) {
                alert('There was a problem saving your password. Please try again later.');
                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
