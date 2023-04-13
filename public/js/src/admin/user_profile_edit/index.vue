<template>
    <div>
        <portal to="header-page-title">Profile</portal>

        <router-view />
    </div>
</template>

<script>
export default {
    metaInfo: {
        title: 'Profile',
    },
}
</script>
