<template>
    <div :class="{ 'mb-2' : setPassword }" class="field-wrap field-wrap-checkbox">
        <input :id="id" v-model="setPassword" type="checkbox">
        <label :for="id">{{ checkboxLabel }}</label>
    </div>

    <field-password v-show="setPassword"
                    :model-value="modelValue"
                    :v="v"
                    :user-data="userData"
                    :show-help="true"
                    :required="setPassword"
                    class="ml-6"
                    autocomplete="new-password"
                    @update:modelValue="$emit('update:modelValue', $event)" />
</template>

<script>
import cuid from 'cuid';
import fieldPassword from '@/common/field_password_with_errors';

export default {
    components: {
        fieldPassword,
    },

    props: {
        modelValue: {
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
        userData: {
            type: Array,
            default () {
                return [];
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
