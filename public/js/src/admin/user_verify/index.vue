<template>
    <div class="form-wrap">
        <form v-if="showForm" @submit.prevent="submit">
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
                            :validation-errors="validationErrors"
                            :show-help="true"
                            label="Password"
                            field="newPassword.first"
                            autocomplete="new-password" />
            <password-field v-model="repeatPassword"
                            :validation-errors="validationErrors"
                            label="Password Again"
                            field="newPassword.second"
                            autocomplete="new-password" />

            <div>
                <button type="submit" class="button">Activate</button>

                <span v-if="status === 'saving'" class="ml-4 text-sm italic">Activating...</span>
            </div>
        </form>

        <div v-if="status === 'saved'" class="alert alert-success" role="alert">
            Your account is now active.
            <router-link :to="{ name: 'login' }" class="pl-4">Login</router-link>
        </div>
    </div>
</template>

<script>
import { logError, hasGraphQlError, hasGraphQlValidationError } from '@/common/lib';
import { UserVerify } from '../queries/user.mutation';

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {},

    props: {},

    data () {
        return {
            status: statuses.LOADED,
            validationErrors: {},
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
            return Object.keys(this.validationErrors).length > 0;
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
                this.validationErrors = {};
                this.password = null;
                this.repeatPassword = null;
                this.invalidToken = false;
                this.tokenExpired = false;

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
