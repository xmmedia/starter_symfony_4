<template>
    <div class="form-wrap">
        <form v-if="showForm" @submit.prevent="submit">
            <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                <li>Please fix the errors below.</li>
            </ul>
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
            Your account is now active.<br>
            <a href="/login" class="pl-4">Continue</a>
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

    watch: {},

    beforeMount () {},

    mounted () {},

    methods: {
        async submit () {
            this.status = statuses.SAVING;

            try {
                const data = {
                    token: this.$route.params.token,
                    password: {
                        first: this.password,
                        second: this.repeatPassword,
                    },
                };

                await userProfileEditRepo.activate(data);

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
