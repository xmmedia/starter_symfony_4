<template>
    <FieldRadios :model-value="modelValue"
                 :values="{ YES: 'Yes', NO: 'No' }"
                 :v="v"
                 :pills="true"
                 @update:model-value="$emit('update:modelValue', $event)">
        <slot></slot>
    </FieldRadios>
</template>

<script setup>
import FieldRadios from '@/common/field_radios.vue';

defineEmits(['update:modelValue']);

defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    v: {
        type: Object,
        default: null,
    },
});
</script>
