<template>
    <RouterView />
</template>

<script setup>
import { useHead } from '@unhead/vue';

useHead({
    title: 'Profile',
});
</script>
