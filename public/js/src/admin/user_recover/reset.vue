<template>
    <div class="form-wrap p-0">
        <div class="p-4">
            <form v-if="showForm" @submit.prevent="submit">
                <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                    <li>Please fix the errors below.</li>
                </ul>
                <ul v-if="invalidToken" class="field-errors mb-4" role="alert">
                    <li>
                        Your reset link is invalid.
                        Please try clicking the button again or copying the link.
                    </li>
                </ul>
                <ul v-if="tokenExpired" class="field-errors mb-4" role="alert">
                    <li>
                        Your link has expired.
                        Please try
                        <router-link :to="{ name: 'user-recover-initiate' }">
                            requesting a new password reset link
                        </router-link>.
                    </li>
                </ul>

                <div class="hidden">
                    <label for="inputEmail">Email</label>
                    <input id="inputEmail"
                           value=""
                           type="email"
                           name="email"
                           autocomplete="username email">
                </div>

                <password-field v-model="newPassword"
                                :validation-errors="validationErrors"
                                :show-help="true"
                                label="New Password"
                                field="newPassword.first"
                                autocomplete="new-password" />
                <password-field v-model="repeatPassword"
                                :validation-errors="validationErrors"
                                label="New Password Again"
                                field="newPassword.second"
                                autocomplete="new-password" />

                <div>
                    <button type="submit" class="button">Set Password</button>
                    <a href="/login" class="form-action">Return to Login</a>

                    <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                    <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
                </div>
            </form>

            <div v-if="status === 'saved'" class="alert alert-success" role="alert">
                Your password has been reset.<br>
                <a href="/login" class="pl-4">Login</a>
            </div>
        </div>
    </div>
</template>

<script>
import { logError, hasGraphQlError, hasGraphQlValidationError } from '@/common/lib';
import { UserRecoverReset } from '../queries/user.mutation';

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    data () {
        return {
            status: statuses.LOADED,
            validationErrors: {},
            invalidToken: false,
            tokenExpired: false,

            newPassword: null,
            repeatPassword: null,
        };
    },

    computed: {
        showForm () {
            return [statuses.LOADED, statuses.SAVING].includes(this.status);
        },
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },
    },

    methods: {
        async submit () {
            this.status = statuses.SAVING;

            try {
                await this.$apollo.mutate({
                    mutation: UserRecoverReset,
                    variables: {
                        token: this.$route.params.token,
                        newPassword: this.newPassword,
                        repeatPassword: this.repeatPassword,
                    },
                });

                this.newPassword = null;
                this.repeatPassword = null;
                this.invalidToken = false;
                this.tokenExpired = false;

                this.status = statuses.SAVED;
                this.validationErrors = {};

                setTimeout(() => {
                    window.location = '/login';
                }, 5000);

            } catch (e) {
                this.validationErrors = {};

                if (hasGraphQlError(e)) {
                    if (hasGraphQlValidationError(e)) {
                        this.validationErrors = e.graphQLErrors[0].validation;
                    } else if (e.graphQLErrors[0].code === 404) {
                        this.invalidToken = true;
                    } else if (e.graphQLErrors[0].code === 405) {
                        this.tokenExpired = true;
                    } else {
                        this.showError(e);
                    }
                } else {
                    this.showError(e);
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        showError (e) {
            logError(e);
            alert('There was a problem saving your password. Please try again later.');
        },
    },
}
</script>
