<template>
    <div>
        <button v-if="state.matches('ready')"
                :disabled="!allow"
                class="button-link form-action"
                type="button"
                @click="sendReset">Send Password Reset</button>
        <div v-if="state.matches('sending')" class="form-action">
            Sending…
        </div>
        <div v-if="state.matches('sent')" class="form-action">
            Sent
        </div>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import { logError } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import { AdminUserSendResetMutation } from '@/admin/queries/admin/user.mutation.graphql';

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
                    mutation: AdminUserSendResetMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                        },
                    },
                });

                this.stateEvent('SENT');

                setTimeout(() => {
                    this.stateEvent('RESET');
                }, 3000);

            } catch (e) {
                logError(e);
                alert('There was a problem sending the reset. Please try again later.');

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
            }
        },
    },
};
</script>
