<template>
    <div class="field-wrap">
        <label :for="id"><slot>Email (Username)</slot></label>

        <field-error v-if="v.$error">
            <template v-if="!v.required">
                An email is required.
            </template>
            <template v-else-if="!v.email">
                This email is invalid.
            </template>
            <template v-else-if="!v.unique">
                This email has already been used.
            </template>
        </field-error>

        <input :id="id"
               :value="value"
               :autofocus="autofocus"
               :autocomplete="autocomplete"
               type="email"
               maxlength="150"
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
        autofocus: {
            type: Boolean,
            default: false,
        },
        autocomplete: {
            type: String,
            default: null,
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
