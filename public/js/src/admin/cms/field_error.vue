<template>
    <field-error v-if="v.$error">
        <template v-if="!required">
            Required
        </template>
        <template v-else-if="!minLength || !maxLength">
            <template v-if="exactLength">
                Must be {{ min }} characters.
            </template>
            <template v-else>
                Must be between {{ min }} and {{ max }} characters.
            </template>
        </template>
    </field-error>
</template>

<script>
import get from 'lodash/get';

export default {
    props: {
        v: {
            type: Object,
            required: true,
        },
    },

    computed: {
        required () {
            return get(this.v, ['required'], true);
        },
        minLength () {
            return get(this.v, ['minLength'], true);
        },
        maxLength () {
            return get(this.v, ['maxLength'], true);
        },

        min () {
            return get(this.v, ['$params', 'minLength', 'min']);
        },
        max () {
            return get(this.v, ['$params', 'maxLength', 'max']);
        },

        exactLength () {
            if (!this.v.$error || !this.min || !this.max) {
                return false;
            }

            return this.min === this.max;
        },
    },
};
</script>
