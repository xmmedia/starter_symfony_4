<template>
    <ul class="flex gap-x-0.5 pl-0 list-none">
        <li class="grow">
            <RouterLink :to="{ name: 'user-profile-edit' }"
                        :class="linkActiveClass('user-profile-edit')"
                        class="block p-4 text-center hover:no-underline font-medium">
                Profile
            </RouterLink>
        </li>
        <li class="grow">
            <RouterLink :to="{ name: 'user-profile-edit-password' }"
                        :class="linkActiveClass('user-profile-edit-password')"
                        class="block p-4 text-center hover:no-underline font-medium">
                Password
            </RouterLink>
        </li>
        <li class="grow">
            <RouterLink :to="{ name: 'user-profile-security' }"
                        :class="linkActiveClass('user-profile-security')"
                        class="block p-4 text-center hover:no-underline font-medium">
                Security
            </RouterLink>
        </li>
    </ul>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const linkActiveClass = (linkRoute) => ({
    // current link
    'bg-blue-100 hover:bg-blue-200' : route.name !== linkRoute,
    // other link
    'bg-blue-600 text-white hover:text-white cursor-default' : route.name === linkRoute,
});
</script>
