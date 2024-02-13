<template>
    <a :href="formatPhone(phoneNumber, 'RFC3966')">
        {{ formatPhone(phoneNumber) }}
    </a>
</template>

<script setup>
import { formatPhone } from '@/common/lib';

defineProps({
    phoneNumber: {
        type: [Object, String],
        required: true,
    },
});
</script>
