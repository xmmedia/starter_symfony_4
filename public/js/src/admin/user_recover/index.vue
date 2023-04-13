<template>
    <div>
        <portal to="header-page-title">Account Recovery</portal>

        <router-view />
    </div>
</template>

<script>
import { useHead } from '@vueuse/head';

export default {
    setup () {
        useHead({
            title: 'Account Recovery',
        });
    },
}
</script>
