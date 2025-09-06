import { defineConfig, globalIgnores } from 'eslint/config';
import globals from 'globals';
import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';

export default defineConfig([
    globalIgnores(['public/js/svgxuse.min.js', 'public/js/src/tinymce/!**!/!*.js']),

    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
    },

    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],

    {
        rules: {
            "no-console": "error",
            "no-debugger": "error",

            "max-len": ["error", {
                "code": 120,
                "ignoreComments": true,
                "ignoreUrls": true,
                "ignoreStrings": true,
            }],

            "comma-dangle": ["error", {
                "arrays": "always-multiline",
                "objects": "always-multiline",
                "imports": "always-multiline",
                "exports": "never",
                "functions": "only-multiline",
            }],

            "vue/multi-word-component-names": "off",
            "space-before-function-paren": ["error", "always"],

            "vue/max-attributes-per-line": ["error", {
                "singleline": 5,
                "multiline": 1,
            }],

            "vue/first-attribute-linebreak": ["error", {
                "singleline": "beside",
                "multiline": "beside",
            }],

            "vue/html-indent": ["error", 4],

            "vue/script-indent": ["error", 4, {
                "switchCase": 1,
            }],

            "vue/html-self-closing": ["error", {
                "html": {
                    "void": "any",
                    "normal": "any",
                    "component": "always",
                },

                "svg": "any",
                "math": "always",
            }],

            "vue/html-closing-bracket-newline": ["error", {
                "singleline": "never",
                "multiline": "never",
            }],

            "vue/multiline-html-element-content-newline": "off",
            "vue/singleline-html-element-content-newline": "off",
        },
    },
]);
