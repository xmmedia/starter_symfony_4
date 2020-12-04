<template>
    <field-password :value="value"
                    :user-data="userData"
                    :show-help="showHelp"
                    :required="false"
                    :autocomplete="autocomplete"
                    @input="$emit('input', $event)">
        <template #default><slot></slot></template>
        <template #errors>
            <field-error v-if="v.$error">
                <template v-if="!required">
                    <slot name="required-msg">A password is required.</slot>
                </template>
                <template v-else-if="!minLength">
                    Passwords must more than {{ v.$params.minLength.min }} characters.
                </template>
                <template v-else-if="!maxLength">
                    The password is too long.
                </template>
                <template v-else-if="!sameAs">
                    The passwords must match.
                </template>
                <template v-else-if="!valid">
                    This password does not match your current password.
                </template>
                <template v-else-if="!strength">
                    This password is not complex enough.
                    Consider adding numbers and special characters.
                </template>
                <template v-else-if="!compromised">
                    It appears that this password was part of a data breach
                    and may not be accepted. Consider using a different password.
                </template>
            </field-error>
        </template>
    </field-password>
</template>

<script>
import has from 'lodash/has';

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
        userData: {
            type: Array,
            default () {
                return [];
            },
        },
    },
    computed: {
        required () {
            return this.vuelidateValue('required');
        },
        minLength () {
            return this.vuelidateValue('minLength');
        },
        maxLength () {
            return this.vuelidateValue('maxLength');
        },
        sameAs () {
            return this.vuelidateValue('sameAs');
        },
        valid () {
            return this.vuelidateValue('valid');
        },
        strength () {
            return this.vuelidateValue('strength');
        },
        compromised () {
            return this.vuelidateValue('compromised');
        },
    },

    methods: {
        hasVuelidateProp (key) {
            return has(this.v, key);
        },

        vuelidateValue (key) {
            if (!this.hasVuelidateProp(key)) {
                return true;
            }

            return this.v[key];
        },
    },
}
</script>
