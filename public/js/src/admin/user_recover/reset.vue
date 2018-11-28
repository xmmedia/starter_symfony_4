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

                <div class="field-wrap form-field_wrap">
                    <label for="inputNewPassword">New Password</label>
                    <field-errors :errors="validationErrors" field="newPassword.first" />
                    <input id="inputNewPassword"
                           v-model="newPassword"
                           type="password"
                           required
                           autocomplete="new-password">
                    <div class="field-help">Must be at least 12 characters long.</div>
                </div>
                <div class="field-wrap form-field_wrap">
                    <label for="inputNewPasswordRepeat">New Password Again</label>
                    <field-errors :errors="validationErrors" field="newPassword.second" />
                    <input id="inputNewPasswordRepeat"
                           v-model="repeatPassword"
                           type="password"
                           required
                           autocomplete="new-password">
                </div>

                <div>
                    <button type="submit" class="button">Set Password</button>
                    <a href="/login" class="form-action">Return to Login</a>

                    <span v-if="status === 'saving'" class="ml-4 text-sm italic">Saving...</span>
                    <span v-else-if="status === 'saved'" class="ml-4 text-sm italic">Saved</span>
                </div>
            </form>

            <div v-if="status === 'saved'" class="alert alert-success" role="alert">
                Your password has been reset.<br>
                <a href="/login" class="pl-4">Continue</a>
            </div>
        </div>
    </div>
</template>

<script>
import { repositoryFactory } from '../repository/factory';
import { logError } from '@/common/lib';

const userProfileEditRepo = repositoryFactory.get('userProfileEdit');

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
                const data = {
                    token: this.$route.params.token,
                    newPassword: {
                        first: this.newPassword,
                        second: this.repeatPassword,
                    },
                };

                await userProfileEditRepo.recoverReset(data);

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
                if (e.response && e.response.status === 400) {
                    this.validationErrors = e.response.data.errors;

                } else if (e.response && e.response.status === 404) {
                    this.invalidToken = true;

                } else if (e.response && e.response.status === 405) {
                    this.tokenExpired = true;

                } else {
                    logError(e);
                    alert('There was a problem saving your password. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
