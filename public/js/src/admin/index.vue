<template>
    <!-- clip: the content's slid right when the mobile menu's open, & a touch browser would otherwise zoom out
         to fit it, making the menu taller than the screen -->
    <div v-if="ready" class="overflow-x-clip">
        <nav class="sidebar_nav-wrap" role="navigation" aria-label="Main">
            <RouterLink :to="logoLinkRoute"
                        class="flex shrink-0 items-center w-40 lg:w-64 h-15 py-2.5
                                border-b border-gray-700 rounded-none ring-offset-gray-900">
                <!-- @todo-symfony alt -->
                <img src="../../../images/logo.svg"
                     width="70"
                     height="70"
                     class="h-10"
                     fetchpriority="high"
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

            <div class="shrink-0 w-40 lg:w-64 mt-auto pt-2 font-extralight text-gray-300">
                <!-- a name too long to fit beside sign out puts it on its own line, on the right -->
                <div class="flex flex-wrap items-end px-4">
                    <div v-if="loggedIn" class="grow max-w-full mb-2 text-sm wrap-break-word">
                        <!-- @todo-symfony if admin only system -->
                        <!--<RouterLink :to="{ name: 'user-profile-edit' }" class="sidebar_nav-bottom_links">
                            {{ profileLinkText }}
                        </RouterLink>-->
                        <a href="/profile/edit" class="sidebar_nav-bottom_links">{{ profileLinkText }}</a>
                    </div>
                    <!-- an icon, so the name has the room: the menu's narrow on small screens -->
                    <div v-if="loggedIn" class="ml-auto pb-2 pl-3">
                        <!-- h-5: the name's line height, so the icon's centred on it -->
                        <a href="/logout" class="sidebar_nav-bottom_links flex items-center h-5" title="Sign Out">
                            <AdminIcon icon="sign-out"
                                       class="size-4"
                                       width="16"
                                       height="16"
                                       aria-hidden="true" />
                            <span class="sr-only">Sign Out</span>
                        </a>
                    </div>
                </div>
                <div class="py-2 pl-4 border-t border-gray-600 text-xs text-gray-400">
                    <!-- @todo-symfony -->
                    ©{{ new Date().getFullYear() }} XM Media Inc.
                </div>
            </div>
        </nav>

        <header class="header-wrap-small">
            <MenuSmall v-if="loggedIn" />
            <RouterLink :to="logoLinkRoute" class="ml-auto rounded-none ring-offset-gray-900">
                <!-- @todo-symfony alt -->
                <img src="../../../images/logo.svg"
                     width="70"
                     height="70"
                     class="h-10 -mt-4 mr-4"
                     fetchpriority="high"
                     alt="Logo">
            </RouterLink>
        </header>
        <div class="content-wrap">
            <header class="header-wrap">
                <!-- Teleport target -->
                <!-- make sure to include a tag inside the Teleport or there will be errors when changing routes -->
                <h1 id="header-page-title" class="header-page_title"></h1>
                <!-- Teleport target -->
                <div id="header-actions" class="header-actions"></div>
            </header>

            <ImpersonationBar v-if="isImpersonating"
                              :name="rootStore.user.name"
                              :email="rootStore.user.email"
                              :user-id="rootStore.user.userId" />

            <main class="p-4" :aria-busy="navigating">
                <!-- *** where the router component is placed *** -->
                <RouterView :key="$route.path" />
            </main>
        </div>

        <SessionExpired />
    </div>

    <LoadingSpinner v-else class="mt-8">Loading…</LoadingSpinner>

    <NavigationProgress />

    <!-- outside the ready check: maintenance can start before the app's loaded -->
    <Maintenance />
</template>

<script setup>
import { computed } from 'vue';
import { useHead } from '@unhead/vue';
import { useRootStore } from './stores/root';
import MenuSubnav from './menu/subnav.vue';
import MenuSmall from './menu/small.vue';
/* eslint-disable no-unused-vars */
import MenuLink from './menu/link.vue';
import ImpersonationBar from '@/common/impersonation_bar.vue';
import Maintenance from '@/common/maintenance.vue';
import NavigationProgress from '@/common/navigation_progress.vue';
import SessionExpired from '@/common/session_expired.vue';
import { navigating } from '@/common/router_helpers';

const rootStore = useRootStore();

useHead({
    title: 'Dashboard',
    // @todo-symfony
    titleTemplate: '%s | Symfony Starter',
});

const ready = computed(() => rootStore.isReady);
const loggedIn = computed(() => rootStore.loggedIn);
const hasRole = computed(() => rootStore.hasRole);

const adminMenuItems = computed(() => ({
    'Users':              'admin-user',
    'Authentication Log': 'admin-auth-log',
    'Messenger Queue':    'admin-messenger-queue',
    ...(import.meta.env.DEV && hasRole.value('ROLE_SUPER_ADMIN') ? { 'Pattern Library': 'pattern-library' } : {}),
}));

const isImpersonating = computed(() => rootStore.user?.isImpersonating ?? false);

const logoLinkRoute = computed(() => ({ name: (loggedIn.value ? 'admin-dashboard' : 'login') }));
const profileLinkText = computed(() => {
    if (loggedIn.value && rootStore.user.name) {
        return rootStore.user.name;
    }

    return 'Profile';
});
</script>
