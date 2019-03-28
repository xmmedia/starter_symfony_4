<template>
    <div class="field-wrap">
        <label :for="id">Email (Username)</label>
        <field-errors :errors="validationErrors" field="email" />
        <input :id="id"
               :value="value"
               type="email"
               maxlength="150"
               required
               autofocus
               @input="$emit('input', $event.target.value)">
    </div>
</template>

<script>
import cuid from 'cuid';

export default {
    props: {
        value: {
            type: String,
            default: null,
        },
        validationErrors: {
            type: Object,
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
