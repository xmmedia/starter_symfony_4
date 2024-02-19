<template>
    <RouterView />
</template>

<script setup>
import { useHead } from '@unhead/vue';

useHead({
    title: 'Users | Admin',
});
</script>
