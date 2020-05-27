<template>
    <div class="form-wrap">
        <portal to="header-actions">
            <div class="header-secondary_actions">
                <router-link :to="{ name: 'admin-page' }">Return to list</router-link>
            </div>
        </portal>

        <h2 class="mt-0">New Page</h2>

        <loading-spinner v-if="state.matches('loading')">
            Loading page…
        </loading-spinner>
        <div v-else-if="state.matches('error')" class="italic text-center">
            There was a problem loading the page. Please try again later.
        </div>

        <form v-else-if="showForm" method="post" @submit.prevent="submit">
            <form-error v-if="$v.$anyError" />

            <page-fields v-model="page" :parent-path="parentPage.path" />

            <admin-button :saving="state.matches('ready.saving')"
                          :saved="state.matches('ready.saved')"
                          :cancel-to="{ name: 'admin-page' }">
                Save
            </admin-button>
        </form>
    </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { v4 as uuid4 } from 'uuid';
import { Machine, interpret } from 'xstate';
import { logError, waitForValidation } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import pageFields from './component/fields';
import pageDefaults from './component/page_defaults';

import { GetPageQuery } from '@/admin/queries/admin/page.query.graphql';
import {
    PageAddMutation,
    PagePublishMutation,
} from '@/admin/queries/admin/page.mutation.graphql';

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
        pageFields,
    },

    mixins: [
        stateMixin,
    ],

    props: {
        parentPageId: {
            type: String,
            default: null,
        },
    },

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,

            page: {
                ...cloneDeep(pageDefaults),
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
        parentPage: {
            query: GetPageQuery,
            variables () {
                return {
                    pageId: this.parentPageId,
                };
            },
            update ({ Page }) {
                this.page.path = Page.path.substring(1)+'/';

                this.stateEvent('LOADED');

                return Page;
            },
            error () {
                this.stateEvent('ERROR');
            },
            skip () {
                return !this.parentPageId;
            },
        },
    },

    validations () {
        return {};
    },

    mounted () {
        if (!this.parentPageId) {
            this.stateEvent('LOADED');
        }
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
                const pageId = uuid4();

                await this.$apollo.mutate({
                    mutation: PageAddMutation,
                    variables: {
                        page: {
                            pageId,
                            path: '/'+this.page.path,
                            template: this.page.template,
                            title: this.page.title,
                            content: JSON.stringify(this.page.content),
                        },
                    },
                });

                if (this.page.published) {
                    await this.$apollo.mutate({
                        mutation: PagePublishMutation,
                        variables: {
                            pageId,
                        },
                    });
                }

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
