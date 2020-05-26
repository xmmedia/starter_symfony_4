<template>
    <div>
        <div class="field-wrap">
            <label for="page-title">Title</label>
            <input id="page-title"
                   v-model="page.title"
                   type="text"
                   @blur="updatePathFromTitle">
        </div>

        <div class="field-wrap">
            <label for="page-path">Path</label>
            <div class="flex">
                <div class="mr-1">/</div>
                <input id="page-path"
                       v-model="page.path"
                       type="text"
                       @input="pathChanged = true">
            </div>
        </div>

        <div class="field-wrap">
            <label for="page-template">Template</label>

            <!--<field-error v-if="v.$error">
                <template v-if="!v.required">
                    An template is required.
                </template>
            </field-error>-->

            <select id="page-template" v-model="page.template">
                <option v-for="template in templates"
                        :key="template.template"
                        :value="template.template">
                    {{ template.name }}
                </option>
            </select>
        </div>
    </div>
</template>

<script>
import slugify from 'slugify';
import { mapState } from 'vuex';

export default {
    components: {
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

                this.page.path = this.parentPath.substring(1) + '/' + slug;
            }
        },
    },
}
</script>
