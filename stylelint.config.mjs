/** @type {import('stylelint').Config} */
export default {
    "extends": [ "stylelint-config-standard", "stylelint-config-tailwindcss" ],
    "rules": {
        "rule-empty-line-before": null,
        "declaration-empty-line-before": null,
        "selector-class-pattern": [
            "^[a-z0-9\\-_]+$",
            {
                "message": "Expected class selector to be kebab-case or BEM-style (lowercase, digits, hyphens, underscores).",
            },
        ],
        "at-rule-no-deprecated": null,
        "no-invalid-position-at-import-rule": [
            true,
            { "ignoreAtRules": [ "config", "source", "theme", "supports", "layer" ] },
        ],
        "custom-property-empty-line-before": null,
        "comment-empty-line-before": null,
        "nesting-selector-no-missing-scoping-root": [true, { "ignoreAtRules": ["utility"] }],
    },
};
