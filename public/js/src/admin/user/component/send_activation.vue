<template>
    <button v-if="state.matches('ready')"
            :disabled="!allow"
            class="button-link form-action"
            type="button"
            @click="sendReset">Resend Activation Email</button>
    <div v-if="state.matches('sending')" class="form-action">
        Sending…
    </div>
    <div v-if="state.matches('sent')" class="form-action">
        Sent
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import { logError } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import { AdminUserSendActivationMutation } from '@/admin/queries/admin/user.mutation.graphql';

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
            on: {
                RESET: 'ready',
            },
        },
    },
});

export default {
    mixins: [
        stateMixin,
    ],

    props: {
        userId: {
            type: String,
            required: true,
        },
        allow: {
            type: Boolean,
            required: true,
        },
    },

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,
        };
    },

    methods: {
        async sendReset () {
            if (!this.allow || !this.state.matches('ready')) {
                return;
            }

            this.stateEvent('SEND');

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserSendActivationMutation,
                    variables: {
                        userId: this.userId,
                    },
                });

                this.stateEvent('SENT');

                setTimeout(() => {
                    this.stateEvent('RESET');
                }, 3000);

            } catch (e) {
                logError(e);
                alert('There was a problem sending the activation link. Please try again later.');

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
            }
        },
    },
}
</script>
