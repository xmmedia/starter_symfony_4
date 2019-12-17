<template>
    <div>
        <form v-if="showForm"
              class="form-wrap"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />
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

            <admin-button :status="status" :cancel-to="{ name: 'login' }">
                Activate
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Login</router-link>
                <template slot="saving">Activating...</template>
            </admin-button>
        </form>

        <div v-if="status === 'saved'" class="alert alert-success max-w-lg" role="alert">
            Your account is now active.
            <router-link :to="{ name: 'login' }" class="pl-4">Login</router-link>
        </div>
    </div>
</template>

<script>
import { hasGraphQlError, waitForValidation } from '@/common/lib';
import { required } from 'vuelidate/lib/validators';
import fieldPassword from '@/common/field_password_with_errors';
import { UserVerify } from '../queries/user.mutation.graphql';
import cloneDeep from 'lodash/cloneDeep';
import userValidation from '@/common/validation/user';

const statuses = {
    LOADED: 'loaded',
    SAVING: 'saving',
    SAVED: 'saved',
};

export default {
    components: {
        fieldPassword,
    },

    data () {
        return {
            status: statuses.LOADED,
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
            this.status = statuses.SAVING;

            this.$v.$touch();
            if (!await this.waitForValidation()) {
                this.status = statuses.LOADED;
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: UserVerify,
                    variables: {
                        token: this.$route.params.token,
                        newPassword: this.password,
                    },
                });

                this.status = statuses.SAVED;
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
            alert('There was a problem activating your account. Please try again later.');
        },
    },
}
</script>
