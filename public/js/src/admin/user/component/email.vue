<template>
    <div class="field-wrap">
        <label for="inputEmail">Email (Username)</label>
        <field-errors :errors="validationErrors" field="email" />
        <input id="inputEmail"
               :value="value"
               type="email"
               maxlength="150"
               required
               autofocus
               @input="$emit('input', $event.target.value)">
    </div>
</template>

<script>
export default {
    props: {
        value: {
            type: String,
            default: null,
        },
        validationErrors: {
            type: Object,
            default: function () {
                return [];
            },
        },
    },
}
</script>
