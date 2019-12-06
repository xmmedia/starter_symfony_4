<template>
    <ul v-if="hasErrors"
        class="field-errors"
        role="alert"
        aria-live="polite">
        <li v-for="(error, i) in flatErrors" :key="i">{{ error }}</li>
    </ul>
</template>

<script>
import get from 'lodash/get';
import toPath from 'lodash/toPath';

export default {
    props: {
        errors: {
            type: [Object, Array],
            default: function () {
                return {};
            },
        },

        // the name of the field in the form
        // will used to find the errors using lodash.get()
        field: {
            type: String,
            default: null,
        },
    },

    computed: {
        hasErrors () {
            if (this.flatErrors === undefined) {
                return false;
            }

            return this.flatErrors.length > 0;
        },

        flatErrors () {
            if (typeof this.errors === 'object' && this.errors.constructor === Array) {
                return this.errors;
            }

            const errors = [];

            const e = get(this.errors, this.errorPaths[0]);
            if (e) {
                errors.push(...e);
            }

            if (this.errorPaths.length > 1) {
                const e = get(this.errors, this.errorPaths[1]);
                if (e) {
                    errors.push(...e);
                }
            }

            return errors;
        },

        errorPaths () {
            if (null === this.field) {
                return ['errors'];
            }

            const path = toPath(this.field);

            if (path.length === 1) {
                return [[this.field, 'errors']];
            }

            return [
                [path[0], 'errors'],
                [path[0], 'children', path[1], 'errors'],
            ];
        },
    },
}
</script>
