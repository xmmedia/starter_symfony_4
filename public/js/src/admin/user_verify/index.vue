<template>
    <div>
        <form v-if="showForm"
              class="form-wrap"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />
            <field-error v-if="invalidToken" class="mb-4">
                Your activation link is invalid.
                Please try clicking the button again or copying the link.
            </field-error>
            <field-error v-if="tokenExpired" class="mb-4">
                Your link has expired. Please contact an administrator.
            </field-error>

            <p :class="{ 'mt-0' : !$v.$anyError && !invalidToken && !tokenExpired }">
                To activate your account, enter a password below.
            </p>

            <div class="hidden">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       type="email"
                       name="email"
                       autocomplete="username email">
            </div>

            <field-password v-model="password"
                            :v="$v.password"
                            :show-help="true"
                            autocomplete="new-password" />
            <field-password v-model="repeatPassword"
                            :v="$v.repeatPassword"
                            autocomplete="new-password">Password Again</field-password>

            <admin-button :saving="state.matches('submitting')"
                          :cancel-to="{ name: 'login' }">
                Activate
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Login</router-link>
                <template slot="saving">Activating…</template>
            </admin-button>
        </form>

        <div v-if="state.matches('verified')" class="alert alert-success max-w-lg" role="alert">
            Your account is now active.
            <router-link :to="{ name: 'login' }" class="pl-4">Login</router-link>
        </div>
    </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { Machine, interpret } from 'xstate';
import { hasGraphQlError, logError, waitForValidation } from '@/common/lib';
import { required } from 'vuelidate/lib/validators';
import fieldPassword from '@/common/field_password_with_errors';
import { UserVerify } from '../queries/user.mutation.graphql';
import stateMixin from '@/common/state_mixin';
import userValidation from '@/admin/validation/user';

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
                SUBMITTED: 'verified',
                ERROR: 'ready',
            },
        },
        verified: {
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

            password: null,
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
            password: {
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
            this.stateEvent('SUBMIT');

            this.$v.$touch();
            if (!await this.waitForValidation()) {
                this.stateEvent('ERROR');
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: UserVerify,
                    variables: {
                        token: this.$route.params.token,
                        password: this.password,
                    },
                });

                this.stateEvent('SUBMITTED');

                this.password = null;
                this.repeatPassword = null;
                this.invalidToken = false;
                this.tokenExpired = false;

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
            alert('There was a problem activating your account. Please try again later.');
        },
    },
}
</script>
