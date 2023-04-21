<template>
    <div>
        <Portal to="header-page-title">Account Recovery</Portal>

        <RouterView />
    </div>
</template>

<script setup>
import { useHead } from '@vueuse/head';

useHead({
    title: 'Account Recovery',
});
</script>
