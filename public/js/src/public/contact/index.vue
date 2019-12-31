<template>
    <div class="form-wrap p-0">
        <div class="p-4">
            <form v-if="showForm" @submit.prevent="submit">
                <form-error v-if="$v.$anyError" />

                <div class="field-wrap">
                    <label for="name">Your Name</label>

                    <field-error v-if="$v.name.$error">
                        <template v-if="!$v.name.required">
                            A name is required.
                        </template>
                        <template v-else-if="!$v.name.minLength || !$v.name.maxLength">
                            Please enter between {{ $v.name.$params.minLength.min }}
                            and {{ $v.name.$params.maxLength.max }} characters.
                        </template>
                    </field-error>

                    <input id="name"
                           v-model="name"
                           :maxlength="$v.name.$params.maxLength.max"
                           type="text"
                           autofocus
                           autocomplete="name">
                </div>

                <field-email v-model="email"
                             :v="$v.email"
                             autocomplete="email">Email address</field-email>

                <div class="field-wrap">
                    <label for="message">Message</label>

                    <field-error v-if="$v.message.$error">
                        <template v-if="!$v.message.required">
                            A message is required.
                        </template>
                        <template v-else-if="!$v.message.minLength || !$v.message.maxLength">
                            Please enter more than {{ $v.message.$params.minLength.min }} characters.
                        </template>
                    </field-error>

                    <textarea id="message"
                              v-model="message"
                              :maxlength="$v.message.$params.maxLength.max"
                              class="h-32" />
                </div>

                <div>
                    <button type="submit" class="button">Send</button>

                    <span v-if="state.matches('sending')"
                          class="ml-4 text-sm italic">Sending...</span>
                </div>
            </form>

            <div v-if="state.matches('sent')" class="alert alert-success" role="alert">
                Thank you for your enquiry.
                We'll be in touch within 2 business days.
            </div>
        </div>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import {
    email,
    minLength,
    maxLength,
    required,
} from 'vuelidate/lib/validators';
import { logError } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import fieldEmail from '@/common/field_email';
import { SendEnquiry } from '../queries/enquiry.mutation.graphql';

const stateMachine = Machine({
    id: 'component',
    initial: 'ready',
    strict: true,
    states: {
        ready: {
            on: {
                SEND: 'sending',
            },
        },
        sending: {
            on: {
                SENT: 'sent',
                ERROR: 'ready',
            },
        },
        sent: {
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

            name: null,
            email: null,
            message: null,
        };
    },

    computed: {
        showForm () {
            return !this.state.done;
        },
    },

    validations () {
        return {
            name: {
                required,
                minLength: minLength(3),
                maxLength: maxLength(50),
            },
            email: {
                required,
                email,
            },
            message: {
                required,
                minLength: minLength(10),
                maxLength: maxLength(10000),
            },
        };
    },

    methods: {
        async submit () {
            this.stateEvent('SEND');

            this.$v.$touch();
            if (this.$v.$anyError) {
                this.stateEvent('ERROR');
                window.scrollTo(0, 0);

                return;
            }

            try {
                await this.$apollo.mutate({
                    mutation: SendEnquiry,
                    variables: {
                        enquiry: {
                            name: this.name,
                            email: this.email,
                            message: this.message,
                        },
                    },
                });

                this.stateEvent('SENT');

            } catch (e) {
                logError(e);
                alert('There was a problem sending your enquiry. Please try again later.');

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
            }
        },
    },
}
</script>
