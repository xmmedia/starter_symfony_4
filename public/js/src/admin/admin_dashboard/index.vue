<template>
    <ul class="flex flex-wrap list-none">
        <portal to="header-page-title">Admin</portal>
        <portal to="header-actions" />

        <li>
            <router-link :to="{ name: 'admin-user' }" class="dashboard-link">
                <svg width="96" height="96"><use xlink:href="#users"></use></svg>
                Users
            </router-link>
        </li>
    </ul>
</template>

<script>
export default {
    mounted () {
        this.$store.dispatch('updatePageTitle', 'Admin');
    },
}
</script>
