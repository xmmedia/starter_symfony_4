<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <form class="p-4" method="post" @submit.prevent="submit">
            <form-error v-if="hasValidationErrors" />
            <div v-if="status === 'saved'" class="alert alert-success mb-4" role="alert">
                <div>
                    Your password has been updated.<br>
                    You will need to login again.
                </div>
                <a :href="loginUrl" class="pl-4">Go to Login</a>
            </div>

            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       :value="$store.state.user.email"
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <password-field v-model="currentPassword"
                            :server-validation-errors="getServerValidationErrors('currentPassword')"
                            label="Current Password"
                            field="currentPassword"
                            autocomplete="current-password" />

            <password-field v-model="newPassword"
                            :server-validation-errors="getServerValidationErrors('newPassword.children.first')"
                            :show-help="true"
                            label="New Password"
                            field="newPassword.first"
                            autocomplete="new-password" />
            <password-field v-model="repeatPassword"
                            :server-validation-errors="getServerValidationErrors('newPassword.children.second')"
                            label="New Password Again"
                            field="newPassword.second"
                            autocomplete="new-password" />

            <div class="mb-4 text-sm">After changing your password, you will need to login again.</div>

            <admin-button :status="status"
                          :cancel-to="{ name: 'user-profile-edit' }">
                Change Password
            </admin-button>
        </form>
    </div>
</template>

<script>
import get from 'lodash/get';
import { hasGraphQlValidationError } from '@/common/lib';
import profileTabs from './component/tabs';
import { ChangePassword } from '../queries/user.mutation';

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        profileTabs,
    },

    data () {
        return {
            status: statuses.LOADED,
            serverValidationErrors: {},

            currentPassword: null,
            newPassword: null,
            repeatPassword: null,
        };
    },

    computed: {
        hasValidationErrors () {
            return Object.keys(this.serverValidationErrors).length > 0;
        },

        loginUrl () {
            return this.$router.resolve({ name: 'login' }).href;
        },
    },

    methods: {
        async submit () {
            this.status = statuses.SAVING;

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

                this.status = statuses.SAVED;
                this.serverValidationErrors = {};

                setTimeout(() => {
                    window.location = this.loginUrl;
                }, 30000);

            } catch (e) {
                if (hasGraphQlValidationError(e)) {
                    this.serverValidationErrors = e.graphQLErrors[0].validation.user;
                } else {
                    alert('There was a problem saving your password. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        getServerValidationErrors (path) {
            return get(this.serverValidationErrors, path);
        },
    },
}
</script>
