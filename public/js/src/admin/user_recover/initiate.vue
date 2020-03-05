<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />
            <field-error v-if="notFound" class="mb-4">
                An account with that email cannot be found.
            </field-error>

            <field-email v-model="email"
                         :v="$v.email"
                         autofocus
                         autocomplete="username email"
                         @input="changed">
                Please enter your email address to search for your account:
            </field-email>

            <admin-button :saving="state.matches('submitting')">
                Search
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Return to Login</router-link>
                <template slot="saving">Requesting…</template>
            </admin-button>
        </form>

        <div v-if="state.matches('requested')" class="alert alert-success max-w-lg" role="alert">
            A password reset link has been sent by email.
            Please follow the instructions within the email to reset your password.
            <router-link :to="{ name: 'login' }" class="w-64 pl-4 text-sm">Return to Login</router-link>
        </div>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import { email, required } from 'vuelidate/lib/validators';
import { hasGraphQlError, logError } from '@/common/lib';
import fieldEmail from '@/common/field_email';
import stateMixin from '@/common/state_mixin';
import { UserRecoverInitiate } from '../queries/user.mutation.graphql';

const stateMachine = Machine({
    id: 'component',
    initial: 'ready',
    strict: true,
    states: {
        ready: {
            on: {
                SUBMIT: 'submitting',
            },
        },
        submitting: {
            on: {
                SUBMITTED: 'requested',
                ERROR: 'ready',
            },
        },
        requested: {
            type: 'final',
        },
    },
});

export default {
    components: {
        fieldEmail,
    },

    mixins: [
        stateMixin,
    ],

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,
            notFound: false,

            email: null,
        };
    },

    computed: {
        showForm () {
            return !this.state.done;
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
            if (!this.state.matches('ready')) {
                return;
            }

            this.stateEvent('SUBMIT');
            this.notFound = false;

            this.$v.$touch();
            if (this.$v.$anyError) {
                this.stateEvent('ERROR');
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

                this.stateEvent('SUBMITTED');

            } catch (e) {
                if (hasGraphQlError(e)) {
                    if (e.graphQLErrors[0].code === 404) {
                        this.notFound = true;
                    } else {
                        logError(e);
                        this.showError();
                    }
                } else {
                    logError(e);
                    this.showError();
                }

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
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
