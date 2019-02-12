<template>
    <div>
        <portal to="header-page-title">User Admin</portal>

        <router-view />
    </div>
</template>

<script>
export default {
    mounted () {
        this.$store.dispatch('updatePageTitle', 'Users | Admin');
    },
}
</script>
