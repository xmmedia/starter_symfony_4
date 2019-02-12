<template>
    <div>
        <portal to="header-page-title">Account Recovery</portal>

        <router-view />
    </div>
</template>

<script>
export default {
    mounted () {
        this.$store.dispatch('updatePageTitle', 'Account Recovery');
    },
}
</script>
