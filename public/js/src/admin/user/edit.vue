<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-user' }">Return to list</router-link>
            </div>
        </portal>

        <h2 class="mt-0">Edit User</h2>

        <loading-spinner v-if="state.matches('loading')">
            Loading user…
        </loading-spinner>
        <div v-else-if="state.matches('error')" class="italic text-center">
            There was a problem loading the user. Please try again later.
        </div>

        <form v-else-if="showForm" method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <field-email v-model="email"
                         :v="$v.email"
                         autocomplete="off"
                         autofocus />

            <field-password v-model="password"
                            :v="$v.password"
                            checkbox-label="Change password"
                            @set-password="setPassword = $event" />

            <field-name v-model="firstName" :v="$v.firstName">First name</field-name>
            <field-name v-model="lastName" :v="$v.lastName">Last name</field-name>

            <field-role v-model="role" :v="$v.role" />

            <admin-button :saving="state.matches('ready.saving')"
                          :saved="state.matches('ready.saved')"
                          :cancel-to="{ name: 'admin-user' }">
                Update User
            </admin-button>

            <ul class="form-extra_actions">
                <li>
                    <activate-verify :user-id="userId"
                                     :verified="verified"
                                     :active="active"
                                     :allow="allowSave"
                                     @activated="active = true"
                                     @deactivated="active = false"
                                     @verified="verified = true" />
                </li>
                <li v-if="active">
                    <send-reset :user-id="userId" :allow="allowSave" />
                </li>
            </ul>
        </form>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import cloneDeep from 'lodash/cloneDeep';
import { logError, waitForValidation } from '@/common/lib';
import stateMixin from '@/common/state_mixin';

import userValidations from './user.validation';

import fieldEmail from '@/common/field_email';
import fieldPassword from './component/password';
import fieldName from '@/common/field_name';
import fieldRole from './component/role';
import activateVerify from './component/activate_verify';
import sendReset from './component/send_reset';

import { GetUserQuery } from '../queries/user.query.graphql';
import { AdminUserUpdateMutation } from '../queries/admin/user.mutation.graphql';

const stateMachine = Machine({
    id: 'component',
    initial: 'loading',
    strict: true,
    states: {
        loading: {
            on: {
                LOADED: 'ready',
                ERROR: 'error',
            },
        },
        ready: {
            initial: 'ready',
            states: {
                ready: {
                    on: {
                        SAVE: 'saving',
                    },
                },
                saving: {
                    on: {
                        SAVED: 'saved',
                        ERROR: 'ready',
                    },
                },
                saved: {
                    type: 'final',
                },
            },
        },
        error: {
            type: 'final',
        },
    },
});

export default {
    components: {
        fieldEmail,
        fieldPassword,
        fieldName,
        fieldRole,
        activateVerify,
        sendReset,
    },

    mixins: [
        stateMixin,
    ],

    props: {
        userId: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,

            email: null,
            setPassword: false,
            password: null,
            role: 'ROLE_USER',
            firstName: null,
            lastName: null,
            verified: false,
            active: false,
        };
    },

    computed: {
        showForm () {
            return this.state.matches('ready') && !this.state.done;
        },
        allowSave () {
            if (!this.showForm) {
                return false;
            }

            return !this.state.matches('ready.saving') && !this.state.matches('ready.saved');
        },
    },

    apollo: {
        user: {
            query: GetUserQuery,
            variables () {
                return {
                    userId: this.userId,
                };
            },
            update ({ User }) {
                this.email = User.email;
                this.role = User.roles[0];
                this.firstName = User.firstName;
                this.lastName = User.lastName;
                this.verified = User.verified;
                this.active = User.active;

                this.stateEvent('LOADED');
            },
            error () {
                this.stateEvent('ERROR');
            },
        },
    },

    validations () {
        return cloneDeep(userValidations);
    },

    methods: {
        waitForValidation,

        async submit () {
            if (!this.allowSave) {
                return;
            }

            this.stateEvent('SAVE');

            this.$v.$touch();
            if (!await this.waitForValidation()) {
                this.stateEvent('ERROR');
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserUpdateMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                            email: this.email,
                            setPassword: this.setPassword,
                            password: this.password,
                            role: this.role,
                            firstName: this.firstName,
                            lastName: this.lastName,
                        },
                    },
                });

                this.stateEvent('SAVED');

                setTimeout(() => {
                    this.$router.push({ name: 'admin-user' });
                }, 1500);

            } catch (e) {
                logError(e);
                alert('There was a problem saving. Please try again later.');

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
            }
        },
    },
}
</script>
