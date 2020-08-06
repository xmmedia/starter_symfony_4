<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-user' }">Return to list</router-link>
            </div>
        </portal>

        <h2 class="mt-0">Add User</h2>
        <form method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <field-email v-model="email"
                         :v="$v.email"
                         autocomplete="off"
                         autofocus />

            <field-password v-model="password"
                            :v="$v.password"
                            :user-data="userDataForPassword"
                            checkbox-label="Set password"
                            @set-password="setPassword = $event" />

            <div class="field-wrap-checkbox">
                <input id="inputActive" v-model="active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <field-name v-model="firstName" :v="$v.firstName">First name</field-name>
            <field-name v-model="lastName" :v="$v.lastName">Last name</field-name>

            <field-role v-model="role" :v="$v.role" />

            <div v-if="!setPassword && active" class="field-wrap-checkbox">
                <input id="inputSendInvite" v-model="sendInvite" type="checkbox">
                <label for="inputSendInvite">Send invite</label>
                <div class="field-help">
                    The user will need to follow the link in the invite email
                    before their account will be fully activated.
                </div>
            </div>

            <admin-button :saving="state.matches('submitting')"
                          :saved="state.matches('saved')"
                          :cancel-to="{ name: 'admin-user' }">
                Add User
            </admin-button>
        </form>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import { v4 as uuid4 } from 'uuid';
import cloneDeep from 'lodash/cloneDeep';
import { logError, waitForValidation } from '@/common/lib';
import stateMixin from '@/common/state_mixin';

import userValidations from './user.validation';

import fieldEmail from '@/common/field_email';
import fieldPassword from './component/password';
import fieldName from '@/common/field_name';
import fieldRole from './component/role';

import { AdminUserAddMutation } from '../queries/admin/user.mutation.graphql';

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
                SUBMITTED: 'saved',
                ERROR: 'ready',
            },
        },
        saved: {
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
    },

    mixins: [
        stateMixin,
    ],

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,

            email: null,
            setPassword: false,
            password: null,
            role: 'ROLE_USER',
            active: true,
            firstName: null,
            lastName: null,
            sendInvite: true,
        };
    },

    computed: {
        userDataForPassword () {
            return [
                this.email,
                this.firstName,
                this.lastName,
            ];
        },
    },

    validations () {
        return cloneDeep(userValidations);
    },

    methods: {
        waitForValidation,

        async submit () {
            if (this.state.matches('submitting')) {
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
                    mutation: AdminUserAddMutation,
                    variables: {
                        user: {
                            userId: uuid4(),
                            email: this.email,
                            setPassword: this.setPassword,
                            password: this.password,
                            role: this.role,
                            active: this.active,
                            firstName: this.firstName,
                            lastName: this.lastName,
                            sendInvite: this.setPassword || !this.active ? false : this.sendInvite,
                        },
                    },
                });

                this.stateEvent('SUBMITTED');

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
