<template>
    <password-field :value="value"
                    :show-help="showHelp"
                    :required="false"
                    :autocomplete="autocomplete"
                    @input="$emit('input', $event)">
        <template #default><slot></slot></template>
        <template #errors>
            <field-error v-if="v.$error">
                <template v-if="!v.required">
                    <slot name="required-msg">A password is required.</slot>
                </template>
                <template v-else-if="!v.minLength">
                    Passwords must more than {{ v.$params.minLength.min }} characters.
                </template>
                <template v-else-if="!v.maxLength">
                    The password is too long.
                </template>
                <template v-else-if="!v.sameAs">
                    The passwords must match.
                </template>
                <template v-else-if="!v.valid">
                    This password does not match your current password.
                </template>
                <template v-else-if="!v.compromised">
                    It appears that this password was part of a data breach
                    and may not be accepted. Consider using a different password.
                </template>
            </field-error>
        </template>
    </password-field>
</template>

<script>
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
        showHelp: {
            type: Boolean,
            default: false,
        },
        v: {
            type: Object,
            required: true,
        },
    },
}
</script>
