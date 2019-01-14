<template>
    <div class="form-wrap p-0">
        <div class="p-4">
            <form v-if="showForm" @submit.prevent="submit">
                <ul v-if="hasValidationErrors" class="field-errors mb-4" role="alert">
                    <li>Please fix the errors below.</li>
                </ul>

                <div class="field-wrap">
                    <label for="name">Your Name</label>
                    <field-errors :errors="validationErrors" field="name" />
                    <input id="name"
                           v-model="name"
                           type="text"
                           required
                           autofocus
                           autocomplete="name">
                </div>

                <div class="field-wrap">
                    <label for="email">Email Address</label>
                    <field-errors :errors="validationErrors" field="email" />
                    <input id="email"
                           v-model="email"
                           type="email"
                           required
                           autocomplete="email">
                </div>

                <div class="field-wrap">
                    <label for="message">Message</label>
                    <field-errors :errors="validationErrors" field="message" />
                    <textarea id="message"
                              v-model="message"
                              required
                              class="h-32"></textarea>
                </div>

                <div>
                    <button type="submit" class="button">Send</button>

                    <span v-if="status === 'sending'" class="ml-4 text-sm italic">Sending...</span>
                </div>
            </form>

            <div v-if="status === 'sent'" class="alert alert-success" role="alert">
                Thank you for your enquiry.
                We'll be in touch within 2 business days.
            </div>
        </div>
    </div>
</template>

<script>
import { logError, hasGraphQlValidationError } from '@/common/lib';
import { SendEnquiry } from '../queries/enquiry.mutation';

const statuses = {
    LOADED: 'loaded',
    SENDING: 'sending',
    SENT: 'sent',
};

export default {
    components: {},

    props: {},

    data () {
        return {
            status: statuses.LOADED,
            validationErrors: {},

            name: null,
            email: null,
            message: null,
        };
    },

    computed: {
        showForm () {
            return [statuses.LOADED, statuses.SENDING].includes(this.status);
        },
        hasValidationErrors () {
            return Object.keys(this.validationErrors).length > 0;
        },
    },

    watch: {},

    beforeMount () {},

    mounted () {},

    methods: {
        async submit () {
            this.validationErrors = {};

            if (!this.name) {
                this.validationErrors['name'] = {
                    errors: ['Please enter your name.'],
                };
            }
            if (!this.email) {
                this.validationErrors['email'] = {
                    errors: ['Please enter your email address.'],
                };
            }
            if (!this.message) {
                this.validationErrors['message'] = {
                    errors: ['Please enter a message.'],
                };
            }

            if (this.hasValidationErrors) {
                window.scrollTo(0, 0);

                return;
            }

            this.status = statuses.SENDING;

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

                this.status = statuses.SENT;
                this.validationErrors = {};

            } catch (e) {


                if (hasGraphQlValidationError(e)) {
                    this.validationErrors = e.graphQLErrors[0].validation['enquiry'];
                } else {
                    logError(e);
                    alert('There was a problem sending your enquiry. Please try again later.');
                }

                window.scrollTo(0, 0);

                this.status = statuses.LOADED;
            }
        },
    },
}
</script>
