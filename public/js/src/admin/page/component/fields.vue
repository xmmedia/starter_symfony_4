<template>
    <div>
        <div class="field-wrap">
            <input v-model="page.title" type="text" @blur="updatePathFromTitle">
        </div>

        <div class="field-wrap">
            <div class="flex">
                <div class="mr-1">/</div>
                <input v-model="page.path" type="text" @input="pathChanged = true">
            </div>
        </div>

        <!-- @todo move directly into this component? then we can have a more properly deal with the loading state -->
        <field-template v-model="page.template" :templates="templates" />
    </div>
</template>

<script>
import slugify from 'slugify';
import fieldTemplate from './field_template';

import { GetTemplatesQuery } from '@/admin/queries/template.query.graphql';

export default {
    components: {
        fieldTemplate,
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

    apollo: {
        templates: {
            query: GetTemplatesQuery,
            update ({ Templates }) {
                const templates = Templates.map((template) => {
                    return {
                        ...template,
                        items: template.items.map((item) => {
                            return {
                                ...item,
                                config: JSON.parse(item.config),
                            };
                        }),
                    };
                });

                return templates;
            },
            error () {
                // @todo
            },
        },
    },

    watch: {
        // @todo deep?
        page (value) {
            this.$emit('input', value);
        },
    },

    beforeMount () {
        this.page = this.value;
    },

    updated () {
        this.page = this.value;
    },

    methods: {
        updatePathFromTitle () {
            if (!this.pathChanged && !this.edit) {
                const slug = slugify(
                    this.page.title.trim(),
                    { lower: true, strict: true }
                );

                this.page.path = this.parentPath + '/' + slug;
            }
        },
    },
}
</script>
