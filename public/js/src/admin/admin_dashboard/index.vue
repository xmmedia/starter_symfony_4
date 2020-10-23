<template>
    <ul class="flex flex-wrap pl-0 list-none">
        <portal to="header-page-title">Admin</portal>
        <portal to="header-actions" />

        <li>
            <router-link :to="{ name: 'admin-user' }" class="dashboard-link">
                <admin-icon icon="users"
                            width="96"
                            height="96" />
                Users
            </router-link>
        </li>
    </ul>
</template>
