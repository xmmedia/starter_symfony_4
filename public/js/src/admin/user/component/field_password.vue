<template>
    <div>
        <FieldCheckbox v-model="setPassword" :class="{ 'mb-2' : setPassword }">
            {{ checkboxLabel }}
        </FieldCheckbox>

        <FieldPassword v-show="setPassword"
                       :model-value="modelValue"
                       :v="v"
                       :user-data="userData"
                       :show-help="true"
                       :required="setPassword"
                       :autocomplete="autocomplete"
                       icon-component="AdminIcon"
                       class="ml-6"
                       @update:model-value="$emit('update:modelValue', $event)" />
    </div>
</template>

<script setup>
import cuid from 'cuid';
import FieldCheckbox from '@/common/field_checkbox.vue';
import FieldPassword from '@/common/field_password_with_errors.vue';
import { ref, watch } from 'vue';

defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    checkboxLabel: {
        type: String,
        default: 'Set Password',
    },
    autocomplete: {
        type: String,
        default: 'new-password',
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
});

const emit = defineEmits(['set-password', 'update:modelValue']);

const setPassword = ref(false);
const id = cuid();

watch(setPassword, (val) => {
    emit('set-password', val);
});
</script>
