<template>
    <ul>
        <li v-for="page in pagesInPath(pages, parentPage.path)" :key="page.pageId">
            <router-link :to="{ name: 'admin-page-edit', params: { pageId: page.pageId } }">
                {{ page.title }}
            </router-link>
            <div class="text-xs text-gray-500">{{ page.path }}</div>

            <page-list v-if="pagesInPath(pages, page.path).length > 0"
                       :pages="pages"
                       :parent-page="page" />
        </li>
        <li>
            <router-link :to="{ name: 'admin-page-add', params: { parentPageId: parentPage.pageId } }">
                + Add
            </router-link>
        </li>
    </ul>
</template>

<script>
import { pagesInPath } from './pages_in_path';

export default {
    name: 'PageList',

    props: {
        pages: {
            type: Array,
            required: true,
        },
        parentPage: {
            type: Object,
            default: null,
        },
    },

    methods: {
        pagesInPath,
    },
};
</script>
