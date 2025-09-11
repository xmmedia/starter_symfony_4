<template>
    <FieldPassword :id="id"
                   :model-value="modelValue"
                   :user-data="userData"
                   :show-help="showHelp"
                   :required="false"
                   :autocomplete="autocomplete"
                   :minlength="hasVuelidateProp(v, 'minLength') ? v.minLength.$params.min : null"
                   :icon-component="iconComponent"
                   @update:model-value="$emit('update:modelValue', $event)">
        <template #default><slot></slot></template>
        <template #errors>
            <FieldError v-if="v.$error && v.$invalid">
                <template v-if="!required">
                    <slot name="required-msg">A password is required.</slot>
                </template>
                <template v-else-if="!minLength">
                    Passwords must more than {{ v.minLength.$params.min }} characters.
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
            </FieldError>
        </template>
        <template #help><slot name="help"></slot></template>
        <template #after><slot name="after"></slot></template>
    </FieldPassword>
</template>

<script setup>
import cuid from 'cuid';
import FieldPassword from './field_password.vue';
import { computed } from 'vue';
import { hasVuelidateProp, vuelidateValue } from '@/common/lib';

defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
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
    iconComponent: {
        type: String,
        default: null,
    },
    id: {
        type: String,
        default: () => cuid(),
    },
});

const required = computed(() => vuelidateValue(props.v, 'required'));
const minLength = computed(() => vuelidateValue(props.v, 'minLength'));
const maxLength = computed(() => vuelidateValue(props.v, 'maxLength'));
const sameAs = computed(() => vuelidateValue(props.v, 'sameAs'));
const valid = computed(() => vuelidateValue(props.v, 'valid'));
const strength = computed(() => vuelidateValue(props.v, 'strength'));
const compromised = computed(() => vuelidateValue(props.v, 'compromised'));
</script>
