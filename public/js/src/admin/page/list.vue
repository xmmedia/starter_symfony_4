<template>
    <div>
        <!--<portal to="header-actions">
            <div class="header-secondary_actions">
                <button type="button" class="button-link" @click="refresh">Refresh</button>
            </div>
        </portal>-->

        <loading-spinner v-if="state.matches('loading')">
            Loading pages…
        </loading-spinner>
        <div v-if="state.matches('error')" class="italic text-center">
            There was a problem loading the page list. Please try again later.
        </div>

        <template v-if="state.matches('loaded')">
            <div v-if="pages.length === 0" class="italic text-center">
                No pages were found.
            </div>

            <ul v-else>
                <li v-for="page in pagesInPath(pages, '')"
                    :key="page.pageId">
                    <router-link :to="{ name: 'admin-page-edit', params: { pageId: page.pageId } }">
                        {{ page.title }}
                    </router-link>
                    <div class="text-xs text-gray-500">{{ page.path }}</div>

                    <page-list v-if="'/' !== page.path && pagesInPath(pages, page.path).length > 0"
                               :pages="pages"
                               :parent-page="page" />
                </li>
                <li>
                    <router-link :to="{ name: 'admin-page-add', params: { parentPageId: null } }">
                        + Add
                    </router-link>
                </li>
            </ul>
        </template>
    </div>
</template>

<script>
import { Machine, interpret } from 'xstate';
import sortBy from 'lodash/sortBy';
import stateMixin from '@/common/state_mixin';
import pageList from './component/page_list';
import { pagesInPath } from './component/pages_in_path';

import { GetPagesQuery } from '../queries/page.query.graphql';

const stateMachine = Machine({
    id: 'component',
    initial: 'loading',
    strict: true,
    states: {
        loading: {
            on: {
                LOADED: 'loaded',
                ERROR: 'error',
            },
        },
        loaded: {
            on: {
                REFRESH: 'loading',
            },
        },
        error: {
            on: {
                REFRESH: 'loading',
            },
        },
    },
});

export default {
    components: {
        pageList,
    },

    mixins: [
        stateMixin,
    ],

    data () {
        return {
            stateService: interpret(stateMachine),
            state: stateMachine.initialState,
        };
    },

    apollo: {
        pages: {
            query: GetPagesQuery,
            update ({ Pages }) {
                this.stateEvent('LOADED');

                return sortBy(Pages, ['path']);
            },
            error () {
                this.stateEvent('ERROR');
            },
        },
    },

    methods: {
        pagesInPath,
    },
}
</script>
