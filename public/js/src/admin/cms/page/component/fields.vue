<template>
    <div>
        <div v-if="!edit" class="field-wrap">
            <label for="page-template">Template</label>

            <!--<field-error v-if="v.$error">
                <template v-if="!v.required">
                    An template is required.
                </template>
            </field-error>-->

            <select id="page-template" v-model="page.template">
                <option :value="null">– Select template –</option>
                <option v-for="_template in templates"
                        :key="_template.template"
                        :value="_template.template">
                    {{ _template.name }}
                </option>
            </select>
            <div class="field-help">
                This controls what the page will look like and what fields are available.
            </div>
        </div>

        <template v-if="page.template">
            <div v-for="(item,i) in template.items" :key="i">
                <component :is="'item-'+item.type"
                           v-model="page.content[item.item].value"
                           :config="item" />
            </div>

            <div class="field-wrap">
                <label for="page-title">Title</label>
                <input id="page-title"
                       v-model="page.title"
                       type="text"
                       maxlength="191"
                       @blur="updatePathFromTitle">
                <div class="field-help">
                    <!-- @todo-symfony -->
                    Displayed as: {{ page.title }} | Symfony Starter<br>
                    Displayed in search results &amp; the browser tab.
                </div>
            </div>

            <div v-if="template.editMetaDescription" class="field-wrap">
                <label for="page-metaDescription">Meta Description</label>
                <textarea id="page-metaDescription"
                          v-model="page.content.metaDescription"
                          class="h-20"
                          maxlength="180" />
                <div class="field-help">
                    This maybe shown in search results.
                    Without it, search engines will pick a piece of the page they feel is relevant.
                    Max length is 180 characters.
                </div>
            </div>

            <div class="field-wrap-checkbox">
                <input id="page-published" v-model="page.published" type="checkbox">
                <label for="page-published">Published/live</label>
            </div>

            <div class="field-wrap-checkbox">
                <input id="page-visibleInSitemap" v-model="page.content.visibleInSitemap" type="checkbox">
                <label for="page-visibleInSitemap">Include in Sitemap</label>
            </div>
        </template>

        <div class="field-wrap" :class="{ 'text-xs mb-2' : edit }">
            <label for="page-path">Path</label>
            <div v-if="!edit" class="flex">
                <div class="mr-1">/</div>
                <input id="page-path"
                       v-model="page.path"
                       type="text"
                       @input="pathChanged = true">
            </div>
            <div v-else>
                <div class="px-2 py-1 text-gray-800 border border-gray-500 rounded-sm bg-gray-400">
                    /{{ page.path }}
                </div>
                <!--<button type="button" class="button-link text-xs">Change</button>-->
            </div>
        </div>

        <div v-if="edit" class="field-wrap text-xs">
            <label for="page-template">Template</label>
            <div class="px-2 py-1 text-gray-800 border border-gray-500 rounded-sm bg-gray-400">
                {{ template.name }}
            </div>
            <!--<button type="button" class="button-link text-xs">Change</button>-->
        </div>
    </div>
</template>

<script>
import slugify from 'slugify';
import some from 'lodash/some';
import { mapState } from 'vuex';
import itemText from '../../item/text';
import itemTextarea from '../../item/textarea';
import itemHtml from '../../item/html';

export default {
    components: {
        itemText,
        itemTextarea,
        itemHtml,
    },

    props: {
        value: {
            type: Object,
            default: null,
        },
        parentPath: {
            type: String,
            default: null,
        },
        edit: {
            type: Boolean,
            default: false,
        },
    },

    data () {
        return {
            page: null,
            pathChanged: false,
        };
    },

    computed: {
        ...mapState('cms', ['templates']),

        template () {
            return this.$store.getters['cms/templateConfig'](this.page.template);
        },
    },

    watch: {
        page (value) {
            this.$emit('input', value);
        },

        'page.template' (value) {
            if (!value) {
                return;
            }

            this.updatePageContentFromTemplate();
        },
    },

    beforeMount () {
        this.page = this.value;

        if (this.page.template) {
            // do this on load as the page may have changed since the last time it was edited
            this.updatePageContentFromTemplate();
        }
    },

    updated () {
        this.page = this.value;
    },

    methods: {
        updatePathFromTitle () {
            if (!this.pathChanged && !this.edit && this.page.title) {
                const slug = slugify(
                    this.page.title.trim(),
                    { lower: true, strict: true }
                );

                this.page.path = this.parentPath.substring(1)+'/'+slug;
            }
        },

        updatePageContentFromTemplate () {
            for (const item of this.template.items) {
                this.page.content[item.item] = {
                    type: item.type,
                    value: null,
                };
            }

            for (const key of Object.keys(this.page.content)) {
                if (['metaDescription', 'visibleInSitemap'].includes(key)) {
                    continue;
                }

                if (!some(this.template.items, { item: key })) {
                    delete this.page.content[key];
                }
            }
        }
    },
}
</script>
