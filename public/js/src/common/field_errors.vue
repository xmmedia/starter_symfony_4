<template>
    <ul v-if="hasErrors" class="field-errors" role="alert">
        <li v-for="error in flatErrors">{{ error }}</li>
    </ul>
</template>

<script>
import flatMap from 'lodash/flatMap';

export default {
    props: {
        errors: {
            type: [Array, Object],
            default: function () {
                return [];
            },
        },
    },
    computed: {
        hasErrors () {
            return this.flatErrors.length > 0;
        },
        flatErrors () {
            if (typeof this.errors === 'object') {
                return flatMap(this.errors);
            } else {
                return this.errors;
            }
        }
    }
}
</script>