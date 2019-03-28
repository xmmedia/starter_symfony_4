<template>
    <div>
        <div :class="{ 'mb-2' : setPassword }" class="field-wrap-checkbox">
            <field-errors :errors="validationErrors" field="setPassword" />
            <input :id="id" v-model="setPassword" type="checkbox">
            <label :for="id">{{ checkboxLabel }}</label>
        </div>

        <password-field v-show="setPassword"
                        :value="value"
                        :validation-errors="validationErrors"
                        :show-help="true"
                        :required="setPassword"
                        label="Password"
                        field="password"
                        class="ml-6"
                        autocomplete="new-password"
                        @input="$emit('input', $event)"/>
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
        validationErrors: {
            type: Object,
            default: function () {
                return {};
            },
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
