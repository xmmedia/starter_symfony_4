module.exports = {
    root: true,
    env: {
        node: true,
        browser: true,
        "cypress/globals": true,
    },
    extends: ['plugin:vue/recommended', 'eslint:recommended'],
    rules: {
        "no-console": "error",
        "no-debugger": "error",
        "max-len": ["error", {
            "code": 120, // so we have a bit of grace
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
            "multiline": "beside"
        }],
        "vue/html-indent": ["error", 4],
        "vue/html-self-closing": [
            "error", {
                "html": {
                    "void": "any",
                    "normal": "any",
                    "component": "always",
                },
                "svg": "any",
                "math": "always",
            },
        ],
        "vue/html-closing-bracket-newline": ["error", {
            "singleline": "never",
            "multiline": "never",
        }],
        "vue/multiline-html-element-content-newline": "off",
        "vue/singleline-html-element-content-newline": "off",
        "vue/component-definition-name-casing": ["error", "kebab-case"],
        // disabled because it warns about the public.js file containing to createApp's
        "vue/one-component-per-file": "off",
    },
    reportUnusedDisableDirectives: true,
    parserOptions: {
        parser: "@babel/eslint-parser",
    },
    plugins: [
        "cypress",
    ],
};
