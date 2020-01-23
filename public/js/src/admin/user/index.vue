<template>
    <div>
        <portal to="header-page-title">Users</portal>

        <router-view />
    </div>
</template>

<script>
export default {
    metaInfo: {
        title: 'Users | Admin',
    },
}
</script>
