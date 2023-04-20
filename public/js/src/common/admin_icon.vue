<template>
    <svg><use :xlink:href="iconsPath+'#'+icon"></use></svg>
</template>

<script setup>
import iconsPath from '@/../../images/icons-admin.svg';

defineProps({
    icon: {
        type: String,
        required: true,
    },
});
</script>
