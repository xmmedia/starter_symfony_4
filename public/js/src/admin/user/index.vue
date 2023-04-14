<template>
    <div>
        <portal to="header-page-title">Users</portal>

        <router-view />
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
