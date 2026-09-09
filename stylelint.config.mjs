import tailwindcss from "@dreamsicle.io/stylelint-config-tailwindcss";

/** @type {import('stylelint').Config} */
export default {
    "extends": [ "stylelint-config-standard", "@dreamsicle.io/stylelint-config-tailwindcss" ],
    // Merged manually: stylelint replaces `languageOptions.syntax` wholesale rather than
    // merging it with the extended config's. Neither at-rule is covered there.
    "languageOptions": {
        "syntax": {
            ...tailwindcss.languageOptions.syntax,
            "atRules": {
                ...tailwindcss.languageOptions.syntax.atRules,
                "config": { "prelude": "<string>" },
                "plugin": { "prelude": "<string>" },
                // the extended config declares `@source` as `<string>` only
                "source": { "prelude": "not? [ <string> | inline( <string> ) ]" },
            },
        },
    },
    "rules": {
        // blank lines between rules & declarations are used for grouping, not required
        "rule-empty-line-before": null,
        "declaration-empty-line-before": null,
        "custom-property-empty-line-before": null,
        "comment-empty-line-before": null,
        "selector-class-pattern": [
            // trailing `:…` segments allow Tailwind variants, e.g. `blocks-wrap\\:text-left`
            "^[a-z0-9\\-_]+(:[a-z0-9\\-_]+)*$",
            {
                "message": "Expected class selector to be kebab-case or BEM-style (lowercase, digits, hyphens, underscores), optionally with Tailwind variant prefixes.",
            },
        ],
        "at-rule-empty-line-before": [
            "always",
            {
                // `@apply` follows declarations inside a rule; at-rules after their own comment
                "ignore": [ "after-comment", "inside-block" ],
                "ignoreAtRules": [ "import", "source" ],
            },
        ],
        // `@plugin` and the `@import`s inside `@layer` aren't misplaced imports
        "no-invalid-position-at-import-rule": [
            true,
            { "ignoreAtRules": [ "config", "plugin", "source", "theme", "layer" ] },
        ],
    },
};
