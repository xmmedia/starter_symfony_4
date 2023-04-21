<template>
    <div v-if="ready">
        <nav class="sidebar_nav-wrap" role="navigation" aria-label="Main">
            <RouterLink :to="logoLinkRoute"
                        class="flex items-center w-40 lg:w-64 border-b border-gray-700
                                focus:ring-offset-gray-900 rounded-none"
                        style="height: 3.75rem; padding: 0.65rem 0;">
                <!-- @todo-symfony alt -->
                <img src="/images/logo.svg"
                     width="70"
                     height="70"
                     class="h-10"
                     alt="Logo">
            </RouterLink>

            <ul class="sidebar_nav-nav">
                <template v-if="loggedIn">
                    <!--<li class="mb-2 lg:mb-0">
                        <RouterLink :to="{ name: 'admin-page' }" class="sidebar_nav-link">
                            <MenuLink label="Pages" icon="gear" />
                        </RouterLink>
                    </li>-->

                    <li v-if="hasRole('ROLE_ADMIN')">
                        <MenuSubnav :items="adminMenuItems" label="Admin" icon="gear" />
                    </li>
                </template>
            </ul>

            <div class="absolute bottom-0 w-40 lg:w-64 pt-2 text-gray-300 font-extralight">
                <div class="flex items-end justify-between px-4">
                    <div v-if="loggedIn" class="w-3/5 lg:w-2/3 mb-2 text-sm" style="overflow-wrap: break-word;">
                        <RouterLink :to="{ name: 'user-profile-edit' }" class="sidebar_nav-bottom_links">
                            {{ profileLinkText }}
                        </RouterLink>
                    </div>
                    <div v-if="loggedIn" class="pb-2 pl-4 text-xs">
                        <a href="/logout" class="sidebar_nav-bottom_links whitespace-nowrap">Sign Out</a>
                    </div>
                </div>
                <div class="py-2 pl-4 text-xs text-gray-400 border-t border-gray-600">
                    <!-- @todo-symfony -->
                    ©{{ copyrightYear }} XM Media Inc.
                </div>
            </div>
        </nav>

        <header class="header-wrap-small">
            <MenuSmall v-if="loggedIn" />
            <RouterLink :to="logoLinkRoute" class="ml-auto rounded-none focus:ring-offset-gray-900">
                <!-- @todo-symfony alt -->
                <img src="/images/logo.svg"
                     width="70"
                     height="70"
                     class="h-10 -mt-4 mr-4"
                     alt="Logo">
            </RouterLink>
        </header>
        <div class="content-wrap">
            <header class="header-wrap">
                <h1 class="header-page_title">
                    <PortalTarget name="header-page-title" />
                </h1>
                <div class="header-actions">
                    <PortalTarget name="header-actions" />
                </div>
            </header>

            <main class="p-4">
                <!-- *** where the router component is placed *** -->
                <RouterView />
            </main>
        </div>

        <ModalsContainer />
    </div>

    <LoadingSpinner v-else class="mt-8" />
</template>

<script setup>
import { computed, ref } from 'vue';
import { useHead } from '@vueuse/head';
import { useRootStore } from './stores/root';
import { ModalsContainer } from 'vue-final-modal';
import MenuSubnav from './menu/subnav';
import MenuSmall from './menu/small';
/* eslint-disable no-unused-vars */
import MenuLink from './menu/link';

const rootStore = useRootStore();

useHead({
    title: 'Dashboard',
    // @todo-symfony
    titleTemplate: '%s | Symfony Starter',
});

const adminMenuItems = ref({
    'Users': 'admin-user',
});

const copyrightYear = ref(new Date().getFullYear());

const ready = computed(() => rootStore.ready);
const loggedIn = computed(() => rootStore.loggedIn);
const hasRole = computed(() => rootStore.hasRole);

const logoLinkRoute = computed(() => {
    if (loggedIn.value) {
        return { name: 'admin-dashboard' };
    }

    return { name: 'login' };
});
const profileLinkText = computed(() => {
    if (loggedIn.value && rootStore.user.name) {
        return rootStore.user.name;
    }

    return 'Profile';
});
</script>
