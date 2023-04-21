<template>
    <div class="form-wrap p-0">
        <div class="p-4">
            <form v-if="showForm" @submit.prevent="submit">
                <FormError v-if="v$.$error && v$.$invalid" />

                <!-- @todo can we use the field_name component? -->
                <div class="field-wrap">
                    <label for="name">Name</label>

                    <FieldError v-if="v$.name.$error && v$.name.$invalid">
                        <template v-if="!v$.name.required">
                            A name is required.
                        </template>
                        <template v-else-if="!v$.name.minLength || !v$.name.maxLength">
                            Please enter between {{ v$.name.minLength.$params.min }}
                            and {{ v$.name.maxLength.$params.max }} characters.
                        </template>
                    </FieldError>

                    <input id="name"
                           v-model="name"
                           :maxlength="v$.name.maxLength.$params.max"
                           type="text"
                           autofocus
                           autocomplete="name">
                </div>

                <FieldEmail v-model="email"
                            :v="v$.email"
                            autocomplete="email">Email address</FieldEmail>

                <div class="field-wrap">
                    <label for="message">Message</label>

                    <FieldError v-if="v$.message.$error && v$.message.$invalid">
                        <template v-if="!v$.message.required">
                            A message is required.
                        </template>
                        <template v-else-if="!v$.message.minLength || !v$.message.maxLength">
                            Please enter more than {{ v$.message.minLength.$params.min }} characters.
                        </template>
                    </FieldError>

                    <textarea id="message"
                              v-model="message"
                              :maxlength="v$.message.maxLength.$params.max"
                              class="h-32" />
                </div>

                <div>
                    <button type="submit" class="button">Send</button>

                    <span v-if="state.matches('sending')"
                          class="ml-4 text-sm italic">Sending…</span>
                </div>
            </form>

            <div v-if="state.matches('sent')" class="alert alert-success" role="alert">
                Thank you for your enquiry.
                We'll be in touch within 2 business days.
            </div>
        </div>
    </div>
</template>

<script setup>
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { useVuelidate } from '@vuelidate/core';
import { maxLength, minLength, required } from '@vuelidate/validators';
import { ref } from 'vue';
import { logError } from '@/common/lib';
import { useMutation } from '@vue/apollo-composable';
import FieldEmail from '@/common/field_email.vue';
import { SendEnquiryMutation } from '../queries/enquiry.mutation.graphql';

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    strict: true,
    predictableActionArguments: true,
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

const { state, send: sendEvent } = useMachine(stateMachine);

const name = ref(null);
const email = ref(null);
const message = ref(null);

const v$ = useVuelidate({
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
}, { name, email, message });

async function submit () {
    sendEvent('SEND');

    if (v$.$validate()) {
        sendEvent('ERROR');
        window.scrollTo(0, 0);

        return;
    }

    try {
        const { mutate: sendEnquiry } = useMutation(SendEnquiryMutation);
        await sendEnquiry({
            enquiry: {
                name: name.value,
                email: email.value,
                message: message.value,
            },
        });

        sendEvent('SENT');

    } catch (e) {
        logError(e);
        alert('There was a problem sending your enquiry. Please try again later.');

        sendEvent('ERROR');
        window.scrollTo(0, 0);
    }
}
</script>
