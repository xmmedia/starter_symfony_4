<template>
    <div>
        <form v-if="showForm"
              class="form-wrap p-4"
              method="post"
              @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />
            <ul v-if="invalidToken" class="field-errors mb-4" role="alert">
                <li>
                    Your reset link is invalid or has expired.
                    Please try clicking the button again or copying the link.
                    Or you can <router-link :to="{ name: 'user-recover-initiate' }">try again</router-link>.
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

            <field-password v-model="newPassword"
                            :v="$v.newPassword"
                            :show-help="true"
                            autocomplete="new-password">New Password</field-password>
            <field-password v-model="repeatPassword"
                            :v="$v.repeatPassword"
                            autocomplete="new-password">New Password Again</field-password>

            <admin-button :status="status">
                Set Password
                <router-link slot="cancel"
                             :to="{ name: 'login' }"
                             class="form-action">Return to Login</router-link>
            </admin-button>
        </form>

        <div v-if="status === 'saved'" class="alert alert-success max-w-lg" role="alert">
            Your password has been reset.
            <router-link :to="{ name: 'login' }" class="pl-4">Login</router-link>
        </div>
    </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { hasGraphQlError, waitForValidation } from '@/common/lib';
import { required } from 'vuelidate/lib/validators';
import userValidation from '@/common/validation/user';
import fieldPassword from '@/common/field_password_with_errors';
import { UserRecoverReset } from '../queries/user.mutation.graphql';

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

            newPassword: null,
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
            this.status = statuses.SAVING;

            this.$v.$touch();
            if (!await this.waitForValidation()) {
                this.status = statuses.LOADED;
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

                this.status = statuses.SAVED;

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
            alert('There was a problem saving your password. Please try again later.');
        },
    },
}
</script>
