<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <form class="p-4" method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <field-email v-model="email"
                         :v="$v.email"
                         autofocus
                         autocomplete="username email"
                         @input="changed">
                Email address
            </field-email>

            <field-name v-model="firstName"
                        :v="$v.firstName"
                        autocomplete="given-name"
                        @input="changed">First name</field-name>
            <field-name v-model="lastName"
                        :v="$v.lastName"
                        autocomplete="family-name"
                        @input="changed">Last name</field-name>

            <admin-button :saving="state.matches('saving')"
                          :saved="state.matches('saved')">
                Save Profile
                <template #cancel>
                    <button v-if="state.matches('edited')"
                            class="form-action button-link"
                            @click.prevent="reset">Reset</button>
                </template>
            </admin-button>
        </form>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import cloneDeep from 'lodash/cloneDeep';
import { logError, waitForValidation } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import fieldEmail from '@/common/field_email';
import fieldName from '@/common/field_name';
import profileTabs from './component/tabs';
import { UserUpdateProfile } from '../queries/user.mutation.graphql';

import userValidations from './user.validation';

const stateMachine = Machine({
    id: 'component',
    initial: 'ready',
    strict: true,
    states: {
        ready: {
            on: {
                SAVE: 'saving',
                EDITED: 'edited',
            },
        },
        edited: {
            on: {
                SAVE: 'saving',
                RESET: 'ready',
            },
        },
        saving: {
            on: {
                SAVED: 'saved',
                ERROR: 'ready',
            },
        },
        saved: {
            on: {
                RESET: 'ready',
                EDITED: 'edited',
            },
        },
    },
});

export default {
    components: {
        profileTabs,
        fieldEmail,
        fieldName,
    },

    mixins: [
        stateMixin,
    ],

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,

            email: this.$store.state.user.email,
            firstName: this.$store.state.user.firstName,
            lastName: this.$store.state.user.lastName,
        };
    },

    beforeRouteLeave (to, from, next) {
        if (this.state.matches('edited')) {
            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                return
            }
        }

        next();
    },

    validations () {
        return {
            email: cloneDeep(userValidations.email),
            firstName: cloneDeep(userValidations.firstName),
            lastName: cloneDeep(userValidations.lastName),
        };
    },

    methods: {
        waitForValidation,

        async submit () {
            if (!this.state.matches('ready') && !this.state.matches('edited')) {
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
                    mutation: UserUpdateProfile,
                    variables: {
                        user: {
                            email: this.email,
                            firstName: this.firstName,
                            lastName: this.lastName,
                        },
                    },
                });

                this.$store.dispatch('updateUser', {
                    email: this.email,
                    firstName: this.firstName,
                    lastName: this.lastName,
                    name: this.firstName + ' ' + this.lastName,
                });

                this.stateEvent('SAVED');

                setTimeout(() => {
                    this.stateEvent('RESET');
                }, 5000);

            } catch (e) {
                logError(e);
                alert('There was a problem saving your profile. Please try again later.');

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
            }
        },

        changed () {
            this.stateEvent('EDITED');
        },

        reset () {
            this.email = this.$store.state.user.email;
            this.firstName = this.$store.state.user.firstName;
            this.lastName = this.$store.state.user.lastName;

            this.stateEvent('RESET');
        },
    },
}
</script>
