<template>
    <ul v-if="hasErrors" class="field-errors" role="alert">
        <li v-for="(error, i) in flatErrors" :key="i">{{ error }}</li>
    </ul>
</template>

<script>
import { get } from 'lodash';

export default {
    props: {
        errors: {
            type: Object,
            default: function () {
                return {};
            },
        },

        // the name of the field in the form
        // will used to find the errors using lodash.get()
        field: {
            type: String,
            required: true,
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
            return get(this.errors, this.errorPath);
        },

        errorPath () {
            return ['children', this.field, 'errors'];
        },
    },
};
</script>
