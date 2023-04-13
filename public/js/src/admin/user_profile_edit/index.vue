<template>
    <div>
        <portal to="header-page-title">Profile</portal>

        <router-view />
    </div>
</template>

<script>
import { useHead } from '@vueuse/head';

export default {
    setup () {
        useHead({
            title: 'Profile',
        });
    },
}
</script>
