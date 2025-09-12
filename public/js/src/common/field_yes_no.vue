<template>
    <FieldRadios v-model="radioValue"
                 :values="{ YES: 'Yes', NO: 'No' }"
                 :v="v"
                 :pills="true"
                 :dark-pills="darkPills">
        <slot></slot>
    </FieldRadios>
</template>

<script setup>
import FieldRadios from '@/common/field_radios.vue';

const radioValue = defineModel({ type: String });

defineProps({
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
