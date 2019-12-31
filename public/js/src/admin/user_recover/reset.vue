<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />
            <field-error v-if="invalidToken" class="mb-4">
                Your reset link is invalid or has expired.
                Please try clicking the button again or copying the link.
                Or you can <router-link :to="{ name: 'user-recover-initiate' }">try again</router-link>.
            </field-error>
            <field-error v-if="tokenExpired" class="mb-4">
                Your link has expired.
                Please try
                <router-link :to="{ name: 'user-recover-initiate' }">
                    requesting a new password reset link
                </router-link>.
            </field-error>

            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       value=""
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <field-password v-model="newPassword"
                            :v="$v.newPassword"
                            :show-help="true"
                            autocomplete="new-password">New Password</field-password>
            <field-password v-model="repeatPassword"
                            :v="$v.repeatPassword"
                            autocomplete="new-password">New Password Again</field-password>

            <admin-button :saving="state.matches('submitting')">
                Set Password
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Return to Login</router-link>
            </admin-button>
        </form>

        <div v-if="state.matches('changed')" class="alert alert-success max-w-lg" role="alert">
            Your password has been reset.
            <router-link :to="{ name: 'login' }" class="pl-4">Login</router-link>
        </div>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import cloneDeep from 'lodash/cloneDeep';
import { hasGraphQlError, logError, waitForValidation } from '@/common/lib';
import { required } from 'vuelidate/lib/validators';
import userValidation from '@/admin/validation/user';
import fieldPassword from '@/common/field_password_with_errors';
import stateMixin from '@/common/state_mixin';
import { UserRecoverReset } from '../queries/user.mutation.graphql';

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
                SUBMITTED: 'changed',
                ERROR: 'ready',
            },
        },
        changed: {
            type: 'final',
        },
    },
});

export default {
    components: {
        fieldPassword,
    },

    mixins: [
        stateMixin,
    ],

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,
            invalidToken: false,
            tokenExpired: false,

            newPassword: null,
            repeatPassword: null,
        };
    },

    computed: {
        showForm () {
            return !this.state.done;
        },
    },

    validations () {
        return {
            newPassword: {
                ...cloneDeep(userValidation.password),
            },
            repeatPassword: {
                required,
            },
        };
    },

    methods: {
        waitForValidation,

        async submit () {
            if (!this.state.matches('ready')) {
                return;
            }

            this.stateEvent('SUBMIT');

            this.$v.$touch();
            if (!await this.waitForValidation()) {
                this.stateEvent('ERROR');
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: UserRecoverReset,
                    variables: {
                        token: this.$route.params.token,
                        newPassword: this.newPassword,
                    },
                });

                this.newPassword = null;
                this.repeatPassword = null;
                this.invalidToken = false;
                this.tokenExpired = false;
                this.$v.$reset();

                this.stateEvent('SUBMITTED');

                setTimeout(() => {
                    window.location = this.$router.resolve({ name: 'login' }).href;
                }, 5000);

            } catch (e) {
                if (hasGraphQlError(e)) {
                    if (e.graphQLErrors[0].code === 404) {
                        this.invalidToken = true;
                    } else if (e.graphQLErrors[0].code === 405) {
                        this.tokenExpired = true;
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
            alert('There was a problem saving your password. Please try again later.');
        },
    },
}
</script>
