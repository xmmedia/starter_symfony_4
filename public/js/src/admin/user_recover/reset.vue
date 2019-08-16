<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="hasValidationErrors" />
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
                            :server-validation-errors="serverValidationErrors"
                            :show-help="true"
                            label="New Password"
                            field="newPassword.first"
                            autocomplete="new-password" />
            <password-field v-model="repeatPassword"
                            :server-validation-errors="serverValidationErrors"
                            label="New Password Again"
                            field="newPassword.second"
                            autocomplete="new-password" />

            <admin-button :status="status">
                Set Password
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Return to Login</router-link>
            </admin-button>
        </form>

        <div v-if="status === 'saved'" class="text-center">
            <div class="mb-4">Your password has been reset.</div>
            <div><router-link :to="{ name: 'login' }">Login</router-link></div>
        </div>
    </div>
</template>

<script>
import { hasGraphQlError, hasGraphQlValidationError } from '@/common/lib';
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
            serverValidationErrors: {},
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
            return Object.keys(this.serverValidationErrors).length > 0;
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
                this.serverValidationErrors = {};

                setTimeout(() => {
                    window.location = this.$router.resolve({ name: 'login' }).href;
                }, 5000);

            } catch (e) {
                this.serverValidationErrors = {};

                if (hasGraphQlError(e)) {
                    if (hasGraphQlValidationError(e)) {
                        this.serverValidationErrors = e.graphQLErrors[0].validation;
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

        showError () {
            alert('There was a problem saving your password. Please try again later.');
        },
    },
}
</script>
