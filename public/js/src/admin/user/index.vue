<template>
    <div>
        <Portal to="header-page-title">Users</Portal>

        <RouterView />
    </div>
</template>

<script>
import { useHead } from '@vueuse/head';

export default {
    setup () {
        useHead({
            title: 'Users | Admin',
        });
    },
}
</script>
