<template>
    <span>
        <template v-if="state.matches('ready')">
            <button v-if="verified"
                    :disabled="!allow"
                    class="button-link form-action"
                    type="button"
                    @click="toggleActive">{{ activeButtonText }}</button>
            <button v-else
                    :disabled="!allow"
                    class="button-link form-action"
                    type="button"
                    @click="verify">Manually Verify User</button>
        </template>

        <div v-if="state.matches('activating')" class="form-action">
            Activating…
        </div>
        <div v-if="state.matches('activated')" class="form-action">
            Activated
        </div>
        <div v-if="state.matches('deactivating')" class="form-action">
            Deactivating…
        </div>
        <div v-if="state.matches('deactivated')" class="form-action">
            Deactivated
        </div>
        <div v-if="state.matches('verifying')" class="form-action">
            Verifying…
        </div>
        <div v-if="state.matches('verified')" class="form-action">
            Verified
        </div>
    </span>
</template>

<script>
import { Machine, interpret } from 'xstate';
import stateMixin from '@/common/state_mixin';
import {
    AdminUserActivateMutation,
    AdminUserVerifyMutation,
} from '@/admin/queries/admin/user.mutation.graphql';
import { logError } from '@/common/lib';

const stateMachine = Machine({
    id: 'component',
    initial: 'ready',
    strict: true,
    states: {
        ready: {
            on: {
                ACTIVATE: 'activating',
                DEACTIVATE: 'deactivating',
                VERIFY: 'verifying',
            },
        },
        activating: {
            on: {
                COMPLETE: 'activated',
                ERROR: 'ready',
            },
        },
        activated: {
            on: {
                RESET: 'ready',
            },
        },
        deactivating: {
            on: {
                COMPLETE: 'deactivated',
                ERROR: 'ready',
            },
        },
        deactivated: {
            on: {
                RESET: 'ready',
            },
        },
        verifying: {
            on: {
                COMPLETE: 'verified',
                ERROR: 'ready',
            },
        },
        verified: {
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
        verified: {
            type: Boolean,
            required: true,
        },
        active: {
            type: Boolean,
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

    computed: {
        allowSave () {
            return this.allow && this.state.matches('ready');
        },
        activeButtonText () {
            return this.active ? 'Deactivate User' : 'Activate User';
        },
    },

    methods: {
        async toggleActive () {
            if (!this.allowSave) {
                return;
            }

            this.stateEvent(this.active ? 'DEACTIVATE' : 'ACTIVATE');

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserActivateMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                            action: this.active ? 'deactivate' : 'activate',
                        },
                    },
                });

                this.$emit(this.active ? 'deactivated' : 'activated');
                this.stateEvent('COMPLETE');

                this.delayedReset();

            } catch (e) {
                logError(e);
                alert('There was a problem toggling the active state. Please try again later.');

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
            }
        },

        async verify () {
            if (!this.allowSave) {
                return;
            }

            this.stateEvent('VERIFY');

            try {
                await this.$apollo.mutate({
                    mutation: AdminUserVerifyMutation,
                    variables: {
                        user: {
                            userId: this.userId,
                        },
                    },
                });

                this.$emit('verified');
                this.stateEvent('COMPLETE');

                this.delayedReset();

            } catch (e) {
                logError(e);
                alert('There was a problem verifying the user. Please try again later.');

                this.stateEvent('ERROR');
                window.scrollTo(0, 0);
            }
        },

        delayedReset () {
            setTimeout(() => {
                this.stateEvent('RESET');
            }, 3000);
        },
    },
};
</script>
