<template>
    <div class="field-wrap">
        <label :for="id">Email (Username)</label>

        <field-errors :errors="serverValidationErrors" />
        <field-error v-if="v.$error">
            <template v-if="!v.required">
                A Email is required.
            </template>
            <template v-else-if="!v.email">
                This Email is invalid.
            </template>
        </field-error>

        <input :id="id"
               :value="value"
               type="email"
               maxlength="150"
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
