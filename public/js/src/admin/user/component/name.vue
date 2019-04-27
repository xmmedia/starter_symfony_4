<template>
    <div class="field-wrap">
        <label :for="id">{{ label }}</label>
        <field-errors :errors="serverValidationErrors" />
        <input :id="id"
               :value="value"
               type="text"
               required
               maxlength="50"
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
    },

    data () {
        return {
            id: cuid(),
        };
    },
}
</script>
