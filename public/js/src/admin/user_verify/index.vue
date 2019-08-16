<template>
    <div>
        <form v-if="showForm"
              class="form-wrap"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="hasValidationErrors" />
            <ul v-if="invalidToken" class="field-errors mb-4" role="alert">
                <li>
                    Your activation link is invalid.
                    Please try clicking the button again or copying the link.
                </li>
            </ul>
            <ul v-if="tokenExpired" class="field-errors mb-4" role="alert">
                <li>
                    Your link has expired. Please contact an administrator.
                </li>
            </ul>

            <p :class="{ 'mt-0' : !hasValidationErrors && !invalidToken && !tokenExpired }">
                To activate your account, enter a password below.
            </p>

            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <password-field v-model="password"
                            :server-validation-errors="serverValidationErrors.password"
                            :show-help="true"
                            label="Password"
                            field="newPassword.first"
                            autocomplete="new-password" />
            <password-field v-model="repeatPassword"
                            :server-validation-errors="serverValidationErrors.repeatPassword"
                            label="Password Again"
                            field="newPassword.second"
                            autocomplete="new-password" />

            <admin-button :status="status" :cancel-to="{ name: 'login' }">
                Activate
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Login</router-link>
                <template slot="saving">Activating...</template>
            </admin-button>
        </form>

        <div v-if="status === 'saved'" class="alert alert-success" role="alert">
            Your account is now active.
            <router-link :to="{ name: 'login' }" class="pl-4">Login</router-link>
        </div>
    </div>
</template>

<script>
import { hasGraphQlError, hasGraphQlValidationError } from '@/common/lib';
import { UserVerify } from '../queries/user.mutation';

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

            password: null,
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
                    mutation: UserVerify,
                    variables: {
                        token: this.$route.params.token,
                        newPassword: this.password,
                        repeatPassword: this.repeatPassword,
                    },
                });

                this.status = statuses.SAVED;
                this.serverValidationErrors = {};
                this.password = null;
                this.repeatPassword = null;
                this.invalidToken = false;
                this.tokenExpired = false;

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
