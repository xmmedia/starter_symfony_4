<template>
    <div class="field-wrap">
        <label :for="id"><slot></slot></label>

        <field-error v-if="v.$error">
            <template v-if="!v.required">
                A <slot></slot> is required.
            </template>
            <template v-else-if="!v.minLength">
                The <slot></slot> must be longer than {{ v.$params.minLength.min }} character.
            </template>
            <template v-else-if="!v.maxLength">
                The <slot></slot> cannot be longer than {{ v.$params.maxLength.max }} characters.
            </template>
        </field-error>

        <input :id="id"
               :value="value"
               :maxlength="v.$params.maxLength.max"
               :autocomplete="autocomplete"
               type="text"
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
