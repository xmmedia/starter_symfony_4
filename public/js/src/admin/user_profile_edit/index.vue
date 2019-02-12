<template>
    <div>
        <portal to="header-page-title">Profile</portal>

        <router-view />
    </div>
</template>

<script>
export default {
    mounted () {
        this.$store.dispatch('updatePageTitle', 'Profile');
    },
}
</script>
