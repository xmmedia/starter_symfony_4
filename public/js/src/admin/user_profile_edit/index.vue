<template>
    <div>
        <Portal to="header-page-title">Profile</Portal>

        <RouterView />
    </div>
</template>

<script setup>
import { useHead } from '@vueuse/head';

useHead({
    title: 'Profile',
});
</script>
