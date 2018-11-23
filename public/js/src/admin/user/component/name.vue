<template>
    <div class="field-wrap">
        <label :for="id">{{ label }}</label>
        <field-errors :errors="validationErrors" :field="field" />
        <input :id="id"
               :value="value"
               type="text"
               required
               maxlength="50"
               @input="$emit('input', $event.target.value)">
    </div>
</template>

<script>
export default {
    props: {
        label: {
            type: String,
            required: true,
        },
        field: {
            type: String,
            required: true,
        },
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

    computed: {
        id () {
            return 'input'+this.field;
        },
    },
}
</script>
