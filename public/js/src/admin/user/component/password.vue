<template>
    <div>
        <div :class="{ 'mb-2' : setPassword }" class="field-wrap-checkbox">
            <input :id="id" v-model="setPassword" type="checkbox">
            <label :for="id">{{ checkboxLabel }}</label>
        </div>

        <password-field v-show="setPassword"
                        :value="value"
                        :show-help="true"
                        :required="setPassword"
                        class="ml-6"
                        autocomplete="new-password"
                        @input="$emit('input', $event)">
            <template #default><slot></slot></template>
            <template #errors>
                <field-error v-if="v.$error">
                    <template v-if="!v.required">
                        A password is required.
                    </template>
                    <template v-else-if="!v.minLength">
                        Passwords must more than {{ v.$params.minLength.min }} characters.
                    </template>
                    <template v-else-if="!v.maxLength">
                        The password is too long.
                    </template>
                    <template v-else-if="!v.compromised">
                        It appears that this password was part of a data breach
                        and may not be accepted. Consider using a different password.
                    </template>
                </field-error>
            </template>
        </password-field>
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
        checkboxLabel: {
            type: String,
            default: 'Set Password',
        },
        v: {
            type: Object,
            required: true,
        },
    },

    data () {
        return {
            setPassword: false,
            id: cuid(),
        };
    },

    watch: {
        setPassword (val) {
            this.$emit('set-password', val);
        },
    },
}
</script>
