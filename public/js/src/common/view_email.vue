<template>
    <a :href="'mailto:'+email">{{ email }}</a>
</template>

<script setup>
defineProps({
    email: {
        type: String,
        required: true,
    },
});
</script>
