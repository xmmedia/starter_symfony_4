<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-user' }">Return to list</router-link>
            </div>
        </portal>

        <h2 class="mt-0">Add User</h2>
        <form method="post" @submit.prevent="submit">
            <form-error v-if="v$.$error && v$.$invalid" />

            <field-email :model-value="email"
                         :v="v$.email"
                         autocomplete="off"
                         autofocus
                         @update:modelValue="setEmailDebounce" />

            <field-password v-model="password"
                            :v="v$.password"
                            :user-data="userDataForPassword"
                            checkbox-label="Set password"
                            @set-password="setPassword = $event" />

            <div class="field-wrap field-wrap-checkbox">
                <input id="inputActive" v-model="active" type="checkbox">
                <label for="inputActive">Active</label>
            </div>

            <field-input v-model.trim="firstName" :v="v$.firstName">First name</field-input>
            <field-input v-model.trim="lastName" :v="v$.lastName">Last name</field-input>

            <field-role v-model="role" :v="v$.role" />

            <div v-if="!setPassword && active" class="field-wrap">
                <div class="field-wrap field-wrap-checkbox">
                    <input id="inputSendInvite" v-model="sendInvite" type="checkbox">
                    <label for="inputSendInvite">Send invite</label>
                </div>
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
import debounce from 'lodash/debounce';
import { useVuelidate } from '@vuelidate/core';
import { logError } from '@/common/lib';
import stateMixin from '@/common/state_mixin';

import userValidations from './user.validation';

import fieldEmail from '@/common/field_email';
import fieldPassword from './component/password';
import fieldInput from '@/common/field_input';
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
        fieldInput,
        fieldRole,
    },

    mixins: [
        stateMixin,
    ],

    setup () {
        return { v$: useVuelidate() };
    },

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
        setEmailDebounce: debounce(function (email) {
            this.setEmail(email);
        }, 100, { leading: true }),
        setEmail (email) {
            this.email = email;
        },

        async submit () {
            if (!this.state.matches('ready')) {
                return;
            }

            this.stateEvent('SUBMIT');

            if (!await this.v$.$validate()) {
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
