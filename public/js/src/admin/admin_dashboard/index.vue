<template>
    <Teleport to="#header-page-title">Admin</Teleport>
    <Teleport to="#header-actions" />

    <ul class="flex flex-wrap pl-0 list-none">
        <li>
            <RouterLink :to="{ name: 'admin-user' }" class="dashboard-link">
                <AdminIcon icon="users" width="96" height="96" />
                Users
            </RouterLink>
        </li>
    </ul>
</template>
