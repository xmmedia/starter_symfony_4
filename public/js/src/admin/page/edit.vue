<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-page' }">Return to list</router-link>
            </div>
        </portal>

        <loading-spinner v-if="state.matches('loading')">
            Loading page…
        </loading-spinner>
        <div v-else-if="state.matches('error')" class="italic text-center">
            There was a problem loading the page. Please try again later.
        </div>

        <form v-else-if="showForm" method="post" @submit.prevent="submit">
            <h2 class="mt-0">{{ page.title }}</h2>

            <form-error v-if="$v.$anyError" />

            <field-template v-model="template" :edit="true" />

            <admin-button :saving="state.matches('ready.saving')"
                          :saved="state.matches('ready.saved')"
                          :cancel-to="{ name: 'admin-page' }">
                Update
            </admin-button>
        </form>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import { logError, waitForValidation } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import fieldTemplate from './component/field_template';

import { GetPageQuery } from '@/admin/queries/page.query.graphql';
import { AdminUserUpdateMutation } from '@/admin/queries/admin/user.mutation.graphql';

const stateMachine = Machine({
    id: 'component',
    initial: 'loading',
    strict: true,
    states: {
        loading: {
            on: {
                LOADED: 'ready',
                ERROR: 'error',
            },
        },
        ready: {
            initial: 'ready',
            states: {
                ready: {
                    on: {
                        SAVE: 'saving',
                    },
                },
                saving: {
                    on: {
                        SAVED: 'saved',
                        ERROR: 'ready',
                    },
                },
                saved: {
                    type: 'final',
                },
            },
        },
        error: {
            type: 'final',
        },
    },
});

export default {
    components: {
        fieldTemplate,
    },

    mixins: [
        stateMixin,
    ],

    props: {
        pageId: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,

            page: {
                template: null,
                page: '/',
            },
        };
    },

    computed: {
        showForm () {
            return this.state.matches('ready') && !this.state.done;
        },
        allowSave () {
            if (!this.showForm) {
                return false;
            }

            return !this.state.matches('ready.saving') && !this.state.matches('ready.saved');
        },
    },

    apollo: {
        page: {
            query: GetPageQuery,
            variables () {
                return {
                    pageId: this.pageId,
                };
            },
            update ({ Page }) {
                Page.content = JSON.parse(Page.content);

                // @todo copy values into local vars
                this.page.template = Page.content.template;
                this.page.path = Page.path.substring(1);

                this.stateEvent('LOADED');

                return Page;
            },
            error () {
                this.stateEvent('ERROR');
            },
        },
    },

    validations () {
        return {};
    },

    methods: {
        waitForValidation,

        async submit () {
            if (!this.allowSave) {
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
                    mutation: AdminUserUpdateMutation,
                    variables: {
                        page: {
                            pageId: this.pageId,
                        },
                    },
                });

                this.stateEvent('SAVED');

                setTimeout(() => {
                    this.$router.push({ name: 'admin-page' });
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
