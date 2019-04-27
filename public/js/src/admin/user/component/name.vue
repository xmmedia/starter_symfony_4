<template>
    <div class="field-wrap">
        <label :for="id">{{ label }}</label>

        <field-errors :errors="serverValidationErrors" />
        <field-error v-if="v.$error">
            <template v-if="!v.required">
                A {{ label }} is required.
            </template>
            <template v-else-if="!v.minLength">
                The {{ label }} must be longer than {{ v.$params.minLength.min }} character.
            </template>
            <template v-else-if="!v.maxLength">
                The {{ label }} cannot be longer than {{ v.$params.maxLength.max }} characters.
            </template>
        </field-error>

        <input :id="id"
               :value="value"
               :maxlength="v.$params.maxLength.max"
               type="text"
               @input="$emit('input', $event.target.value)">
    </div>
</template>

<script>
import cuid from 'cuid';

export default {
    props: {
        label: {
            type: String,
            required: true,
        },
        value: {
            type: String,
            default: null,
        },
        serverValidationErrors: {
            type: [Object, Array],
            default: function () {
                return {};
            },
        },
        v: {
            type: Object,
            required: true,
        },
    },

    data () {
        return {
            id: cuid(),
        };
    },
}
</script>
