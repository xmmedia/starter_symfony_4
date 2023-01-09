<template>
    <div class="form-wrap p-0">
        <profile-tabs />

        <form class="p-4" method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <field-email :value="email"
                         :v="$v.email"
                         autofocus
                         autocomplete="username email"
                         @input="setEmailDebounce">
                Email address
            </field-email>

            <field-input v-model.trim="firstName"
                        :v="$v.firstName"
                        autocomplete="given-name"
                        @input="changed">First name</field-input>
            <field-input v-model.trim="lastName"
                        :v="$v.lastName"
                        autocomplete="family-name"
                        @input="changed">Last name</field-input>

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
import debounce from 'lodash/debounce';
import cloneDeep from 'lodash/cloneDeep';
import { logError, waitForValidation } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import fieldEmail from '@/common/field_email';
import fieldInput from '@/common/field_input';
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
        fieldInput,
    },

    mixins: [
        stateMixin,
    ],

    beforeRouteLeave (to, from, next) {
        if (this.state.matches('edited')) {
            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                return
            }
        }

        next();
    },

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,

            email: this.$store.state.user.email,
            firstName: this.$store.state.user.firstName,
            lastName: this.$store.state.user.lastName,
        };
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

        setEmailDebounce: debounce(function (email) {
            this.setEmail(email);
        }, 100, { leading: true }),
        setEmail (email) {
            this.email = email;
            this.changed();
        },

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
                const data = {
                    email: this.email,
                    firstName: this.firstName,
                    lastName: this.lastName,
                };

                await this.$apollo.mutate({
                    mutation: UserUpdateProfile,
                    variables: {
                        user: data,
                    },
                });

                this.$store.dispatch('updateUser', {
                    ...data,
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
