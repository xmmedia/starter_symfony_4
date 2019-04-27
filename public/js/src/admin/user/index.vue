<template>
    <div>
        <portal to="header-page-title">Users</portal>

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
