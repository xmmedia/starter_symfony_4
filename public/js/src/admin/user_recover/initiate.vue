<template>
    <div class="form-wrap p-0">
        <div class="p-4">
            <form v-if="showForm" @submit.prevent="submit">
                <form-error v-if="hasValidationErrors" />
                <ul v-if="notFound" class="field-errors mb-4" role="alert">
                    <li>An account with that email cannot be found.</li>
                </ul>

                <div class="field-wrap">
                    <label for="inputEmail">Please enter your email address to search for your account.</label>
                    <input id="inputEmail"
                           v-model="email"
                           type="email"
                           required
                           autofocus
                           autocomplete="username email">
                </div>

                <div>
                    <button type="submit" class="button">Search</button>
                    <router-link :to="{ name: 'login' }" class="form-action">Return to Login</router-link>

                    <span v-if="status === 'saving'" class="ml-4 text-sm italic">Requesting...</span>
                </div>
            </form>

            <div v-if="status === 'saved'" class="text-center">
                <div class="mb-4">
                    A password reset link has been sent by email.
                    Please follow the instructions within the email to reset your password.
                </div>
                <div><router-link :to="{ name: 'login' }">Return to Login</router-link></div>
            </div>
        </div>
    </div>
</template>

<script>
import { hasGraphQlError, hasGraphQlValidationError } from '@/common/lib';
import { UserRecoverInitiate } from '../queries/user.mutation';

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
            notFound: false,

            email: null,
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
                    mutation: UserRecoverInitiate,
                    variables: {
                        email: this.email,
                    },
                });

                this.email = null;

                this.status = statuses.SAVED;
                this.serverValidationErrors = {};
                this.notFound = false;

            } catch (e) {
                this.serverValidationErrors = {};

                if (hasGraphQlError(e)) {
                    if (hasGraphQlValidationError(e)) {
                        this.serverValidationErrors = e.graphQLErrors[0].validation;
                    } else if (e.graphQLErrors[0].code === 404) {
                        this.notFound = true;
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
            alert('There was a problem requesting a password reset. Please try again later.');
        },
    },
}
</script>
