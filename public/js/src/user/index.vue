<template>
    <header class="relative flex flex-col items-center gap-y-4 mt-2 md:mt-8">
        <div v-if="loggedIn" class="mt-8 md:mt-0"></div>
        <RouterLink :to="{ name: 'dashboard' }" class="mt-4 md:mt-0">
            <!-- @todo-symfony -->
            <img src="../../../images/logo.svg"
                 width="200"
                 height="84"
                 alt="Logo"
                 loading="lazy"
                 decoding="async">
        </RouterLink>

        <div class="w-full mt-4 py-6 bg-gray-900">
            <div v-if="loggedIn" class="header_nav-nav">
                <RouterLink :to="{ name: 'dashboard' }" class="px-4">Dashboard</RouterLink>
                <RouterLink :to="{ name: 'user-profile-edit' }"
                            class="flex justify-center lg:justify-start items-center px-4">
                    <PublicIcon icon="user" width="12" height="12" class="mr-1 fill-current" />
                    {{ rootStore.user.name }}
                </RouterLink>
                <a v-if="rootStore.hasRole('ROLE_ADMIN')" href="/admin" class="px-4">
                    Admin
                </a>
                <a href="/logout" class="px-4">Sign Out</a>
            </div>
            <div v-else class="header_nav-nav">
                <RouterLink :to="{ name: 'login' }"
                            class="text-gray-300 hover:text-gray-200 hover:underline">
                    Sign In
                </RouterLink>
            </div>
        </div>
    </header>

    <main class="w-full">
        <!-- *** where the router component is placed *** -->
        <RouterView :key="$route.path" />
    </main>

    <footer class="px-4 2xl:px-0 pt-8 pb-16 bg-gray-900 text-sm">
        <div class="footer-content max-w-7xl mx-auto">
            <div class="mt-8 md:mt-0 text-right">
                <!-- @todo-symfony -->
                <div class="pt-6 mb-8 text-white">
                    © {{ new Date().getFullYear() }} XM Media Inc. All Rights Reserved
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { useHead } from '@unhead/vue';
import { computed } from 'vue';
import { useRootStore } from './stores/root';

const rootStore = useRootStore();

useHead({
    // @todo-symfony
    title: 'Symfony Starter',
    titleTemplate: '%s | Symfony Starter',
});

const loggedIn = computed(() => rootStore.loggedIn);
</script>
