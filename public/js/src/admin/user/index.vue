<template>
    <div>
        <Portal to="header-page-title">Users</Portal>

        <RouterView />
    </div>
</template>

<script setup>
import { useHead } from '@unhead/vue';

useHead({
    title: 'Users | Admin',
});
</script>
