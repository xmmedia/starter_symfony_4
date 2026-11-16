import { defineConfig, globalIgnores } from 'eslint/config';
import globals from 'globals';
import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import stylistic from '@stylistic/eslint-plugin';
import graphqlPlugin from '@graphql-eslint/eslint-plugin';

const stylisticConfig = stylistic.configs.customize({
    indent: 4,
    quotes: 'single',
    semi: true,
    jsx: false,
    braceStyle: '1tbs',
    arrowParens: true,
});

export default defineConfig([
    globalIgnores([
        // git worktrees Claude Code checks out here: a whole second copy of the project
        '.claude/',
        '.yarn/',
        'config/',
        'coverage/',
        'public/build/',
        'public/js/src/tinymce/**/*.js',
        'schema.graphql',
        'var/',
        'vendor/',
    ]),

    {
        languageOptions: {
            globals: globals.browser,
        },
    },

    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],

    {
        files: ['**/*.{js,mjs,cjs,vue}'],
        plugins: stylisticConfig.plugins,
        rules: {
            ...stylisticConfig.rules,

            "no-console": "error",
            "no-debugger": "error",

            "@stylistic/max-len": ["error", {
                "code": 120,
                "ignoreComments": true,
                "ignoreUrls": true,
                "ignoreStrings": true,
            }],

            "@stylistic/comma-dangle": ["error", {
                "arrays": "always-multiline",
                "objects": "always-multiline",
                "imports": "always-multiline",
                "exports": "never",
                "functions": "only-multiline",
            }],

            "@stylistic/space-before-function-paren": ["error", "always"],
            // either way reads fine: a map of labels quotes them all, attrs quote only the aria-* ones
            "@stylistic/quote-props": "off",

            // the code has both [a] & [ a ]
            "@stylistic/array-bracket-spacing": "off",
            // minimum allows values aligned with extra spaces
            "@stylistic/key-spacing": ["error", { "mode": "minimum" }],
            "@stylistic/no-multi-spaces": ["error", {
                "exceptions": {
                    "Property": true,
                    "VariableDeclarator": true,
                    "ImportDeclaration": true,
                },
            }],
            // a blank line before } catch is allowed, but only some try blocks have one
            "@stylistic/padded-blocks": "off",

            "vue/multi-word-component-names": "off",
            "vue/object-curly-spacing": ["error", "always"],
            "vue/space-infix-ops": "error",
            "vue/comma-spacing": "error",

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

    // vue/script-indent handles the <script> blocks
    {
        files: ['**/*.vue'],
        rules: {
            "@stylistic/indent": "off",
        },
    },

    // the config files at the root run in Node & keep the quotes their packages' docs use
    {
        files: ['*.{js,mjs,cjs}'],
        languageOptions: {
            globals: globals.node,
        },
        rules: {
            "@stylistic/quotes": "off",
        },
    },

    // GraphQL files configuration
    {
        files: ['**/*.graphql'],
        languageOptions: {
            parser: graphqlPlugin.parser,
        },
        plugins: {
            '@graphql-eslint': graphqlPlugin,
        },
        rules: {
            ...graphqlPlugin.configs['flat/schema-recommended'].rules,
            ...graphqlPlugin.configs['flat/operations-recommended'].rules,
            "@graphql-eslint/naming-convention": [
                'error',
                {
                    VariableDefinition: "camelCase",
                    OperationDefinition: {
                        style: "PascalCase",
                    },
                    FragmentDefinition: {
                        style: "PascalCase",
                    },
                },
            ],
            "@graphql-eslint/unique-operation-name": "off",
        },
    },
]);
