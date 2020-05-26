<template>
    <div>
        <portal to="header-page-title">Pages</portal>

        <router-view />
    </div>
</template>

<script>
export default {
    metaInfo: {
        title: 'Pages | Admin',
    },
}
</script>
