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
            <div class="flex">
                <h2 class="mt-0">Page: {{ pageOriginal.title }}</h2>
                <div class="text-right flex-grow text-sm">
                    <a :href="pageUrl" target="_blank">View page</a>
                </div>
            </div>

            <form-error v-if="$v.$anyError" />

            <page-fields v-model="page" :edit="true" :v="$v.page" />

            <admin-button :saving="state.matches('ready.saving')"
                          :saved="state.matches('ready.saved')"
                          :cancel-to="{ name: 'admin-page' }">
                Update
            </admin-button>
        </form>
    </div>
</template>

<script>
import cloneDeep from 'lodash/cloneDeep';
import { Machine, interpret } from 'xstate';
import { logError, waitForValidation } from '@/common/lib';
import stateMixin from '@/common/state_mixin';
import pageFields from './component/fields';
import pageDefaults from './component/page_defaults';
import pageValidation from './component/page_validation';

import { GetPageQuery } from '@/admin/queries/admin/page.query.graphql';
import {
    PageUpdateMutation,
    PagePublishMutation,
    PageUnpublishMutation,
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

        pageUrl () {
            if (!this.pageOriginal) {
                return null;
            }

            return this.$store.state.cms.rootUrl+this.pageOriginal.path;
        },
    },

    apollo: {
        pageOriginal: {
            query: GetPageQuery,
            variables () {
                return {
                    pageId: this.pageId,
                };
            },
            update ({ Page }) {
                if (!Page) {
                    this.stateEvent('ERROR');

                    return Page;
                }

                Page.content = JSON.parse(Page.content);

                this.page.path = Page.path.substring(1);
                this.page.template = Page.template.template;
                this.page.title = Page.title;
                this.page.published = Page.published;
                this.page.content = {
                    ...cloneDeep(this.page.content),
                    ...cloneDeep(Page.content),
                };

                this.stateEvent('LOADED');

                return Page;
            },
            error () {
                this.stateEvent('ERROR');
            },
        },
    },

    validations () {
        return {
            page: pageValidation(this.$store.getters['cms/templateConfig'](this.page.template), true),
        };
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
                    mutation: PageUpdateMutation,
                    variables: {
                        page: {
                            pageId: this.pageId,
                            title: this.page.title,
                            content: JSON.stringify(this.page.content),
                        },
                    },
                });

                let publishMutation;
                if (!this.pageOriginal.published && this.page.published) {
                    // not published before, published now
                    publishMutation = PagePublishMutation;
                } else if (this.pageOriginal.published && !this.page.published) {
                    // published before, not published now
                    publishMutation = PageUnpublishMutation;
                }

                if (publishMutation) {
                    await this.$apollo.mutate({
                        mutation: publishMutation,
                        variables: {
                            pageId: this.pageId,
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
