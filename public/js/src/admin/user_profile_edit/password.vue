<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <div class="p-4">
            <form @submit.prevent="submit">
                <form-error v-if="hasValidationErrors" />
                <div v-if="status === 'saved'" class="alert alert-success mb-4" role="alert">
                    <div>
                        Your password has been updated.<br>
                        You will need to login again.
                    </div>
                    <router-link :to="{ name: 'login' }" class="pl-4">Go to Login</router-link>
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
                                label="New Password"
                                field="newPassword.first"
                                autocomplete="new-password" />
                <password-field v-model="repeatPassword"
                                :server-validation-errors="getServerValidationErrors('newPassword.children.second')"
                                label="New Password Again"
                                field="newPassword.second"
                                autocomplete="new-password" />

                <div class="mb-4 text-sm">After changing your password, you will need to login again.</div>

                <div>
                    <button type="submit" class="button">Change Password</button>
                    <router-link :to="{ name: 'user-profile-edit' }"
                                 class="form-action">Cancel</router-link>

                    <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                    <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import get from 'lodash/get';
import { logError, hasGraphQlValidationError } from '@/common/lib';
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

            } catch (e) {
                if (hasGraphQlValidationError(e)) {
                    this.serverValidationErrors = e.graphQLErrors[0].validation.user;
                } else {
                    logError(e);
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
