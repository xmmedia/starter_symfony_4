<template>
    <div class="field-wrap">
        <label :for="id">{{ config.name }}</label>
        <field-error :v="v" />
        <editor v-model="currentValue"
                :init="tinymceConfig"
                tinymce-script-src="/js/src/tinymce/tinymce.min.js" />
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-if="config.help" class="field-help" v-html="config.help"></div>
    </div>
</template>

<script>
/* global __webpack_public_path__ */
import cuid from 'cuid';
import fieldError from '../field_error';
import editor from '@tinymce/tinymce-vue';
import tinymceConfig from './tinymce_config';

export default {
    components: {
        fieldError,
        editor,
    },

    props: {
        value: {
            type: String,
            default: null,
        },
        config: {
            type: Object,
            required: true,
        },
        v: {
            type: Object,
            required: true,
        },
    },

    data () {
        return {
            currentValue: null,
            id: cuid(),
            publicCssPath: null,
        };
    },

    computed: {
        tinymceConfig () {
            return {
                ...tinymceConfig,
                content_css: [
                    this.publicCssPath,
                ],
            };
        },
    },

    watch: {
        currentValue (value) {
            this.$emit('input', value);
        },
    },

    beforeMount () {
        this.currentValue = this.value;

        this.findPublicCssPath();
    },

    updated () {
        // this.currentValue = this.value;
    },

    methods: {
        async findPublicCssPath () {
            if (!module.hot) {
                const response = await fetch('/build/manifest.json');
                if (response.ok) {
                    const manifest = await response.json();
                    this.publicCssPath = manifest['build/public.css'];
                }
            } else {
                this.publicCssPath = __webpack_public_path__+'public.css';
            }
        },
    },
};
</script>
