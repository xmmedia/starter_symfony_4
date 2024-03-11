<template>
    <FieldRadios :model-value="modelValue"
                 :values="{ YES: 'Yes', NO: 'No' }"
                 :v="v"
                 :pills="true"
                 :dark-pills="darkPills"
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
    darkPills: {
        type: Boolean,
        default: false,
    },
    v: {
        type: Object,
        default: null,
    },
});
</script>
