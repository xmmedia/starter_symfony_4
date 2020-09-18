<template>
    <ul class="flex flex-wrap pl-0 list-none">
        <portal to="header-page-title">Admin</portal>
        <portal to="header-actions" />

        <li>
            <router-link :to="{ name: 'admin-user' }" class="dashboard-link">
                <svg width="96" height="96"><use :xlink:href="iconsPath+'#users'"></use></svg>
                Users
            </router-link>
        </li>
    </ul>
</template>

<script>
import iconsPath from '@/../../images/icons-admin.svg';

export default {
    data () {
        return {
            iconsPath,
        };
    },
}
</script>
