<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />
            <ul v-if="notFound" class="field-errors mb-4" role="alert">
                <li>An account with that email cannot be found.</li>
            </ul>

            <field-email v-model="email"
                         :v="$v.email"
                         autofocus
                         autocomplete="username email"
                         @input="changed">
                Please enter your email address to search for your account:
            </field-email>

            <admin-button :status="status">
                Search
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Return to Login</router-link>
                <template slot="saving">Requesting...</template>
            </admin-button>
        </form>

        <div v-if="status === 'saved'" class="alert alert-success max-w-lg" role="alert">
            A password reset link has been sent by email.
            Please follow the instructions within the email to reset your password.
            <router-link :to="{ name: 'login' }" class="w-64 pl-4 text-sm">Return to Login</router-link>
        </div>
    </div>
</template>

<script>
import { email, required } from 'vuelidate/lib/validators';
import { hasGraphQlError } from '@/common/lib';
import fieldEmail from '@/common/field_email';
import { UserRecoverInitiate } from '../queries/user.mutation.graphql';

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        fieldEmail,
    },

    data () {
        return {
            status: statuses.LOADED,
            notFound: false,

            email: null,
        };
    },

    computed: {
        showForm () {
            return [statuses.LOADED, statuses.SAVING].includes(this.status);
        },
    },

    validations () {
        return {
            email: {
                required,
                email,
            },
        };
    },

    methods: {
        async submit () {
            this.status = statuses.SAVING;
            this.notFound = false;

            this.$v.$touch();
            if (this.$v.$anyError) {
                this.status = statuses.LOADED;
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: UserRecoverInitiate,
                    variables: {
                        email: this.email,
                    },
                });

                this.email = null;
                this.$v.$reset();

                this.status = statuses.SAVED;
                this.notFound = false;

            } catch (e) {
                if (hasGraphQlError(e)) {
                    if (e.graphQLErrors[0].code === 404) {
                        this.notFound = true;
                    } else {
                        this.showError();
                    }
                } else {
                    this.showError();
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },

        showError () {
            alert('There was a problem requesting a password reset. Please try again later.');
        },

        changed () {
            this.notFound = false;
        },
    },
}
</script>
