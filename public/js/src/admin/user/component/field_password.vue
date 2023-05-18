<template>
    <div>
        <div :class="{ 'mb-2' : setPassword }" class="field-wrap field-wrap-checkbox">
            <input :id="id" v-model="setPassword" type="checkbox">
            <label :for="id">{{ checkboxLabel }}</label>
        </div>

        <FieldPassword v-show="setPassword"
                       :model-value="modelValue"
                       :v="v"
                       :user-data="userData"
                       :show-help="true"
                       :required="setPassword"
                       class="ml-6"
                       autocomplete="new-password"
                       @update:modelValue="$emit('update:modelValue', $event)" />
    </div>
</template>

<script setup>
import cuid from 'cuid';
import FieldPassword from '@/common/field_password_with_errors';
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
