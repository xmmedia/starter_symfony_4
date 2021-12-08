<template>
    <div v-if="ready">
        <nav class="sidebar_nav-wrap" role="navigation" aria-label="Main">
            <router-link :to="logoLinkRoute"
                         class="flex items-center w-40 lg:w-64 border-b border-gray-700 rounded-none
                            focus:bg-gray-700 focus:m-0"
                         style="height: 3.75rem; padding: 0.65rem 0;">
                <!-- @todo-symfony alt -->
                <img src="/images/logo.svg"
                     width="70"
                     height="70"
                     class="h-10 ml-4"
                     alt="Logo">
            </router-link>

            <ul class="sidebar_nav-nav">
                <template v-if="loggedIn">
                    <!--<li class="mb-2 focus:mb-2 lg:mb-0">
                        <router-link :to="{ name: 'admin-page' }"
                                     class="sidebar_nav-link">
                            <menu-link label="Pages" icon="gear" />
                        </router-link>
                    </li>-->

                    <li v-if="hasRole('ROLE_ADMIN')">
                        <menu-subnav :items="adminMenuItems" label="Admin" icon="gear" />
                    </li>
                </template>
            </ul>

            <div class="absolute bottom-0 w-40 lg:w-64 pt-2 text-gray-300 font-extralight">
                <div class="flex items-end justify-between px-4">
                    <div v-if="loggedIn" class="w-3/5 lg:w-2/3 mb-2 text-sm" style="overflow-wrap: break-word;">
                        <router-link :to="{ name: 'user-profile-edit' }" class="sidebar_nav-bottom_links">
                            {{ profileLinkText }}
                        </router-link>
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
            <menu-small v-if="loggedIn" />
            <router-link :to="logoLinkRoute" class="ml-auto rounded-none focus:bg-gray-700">
                <!-- @todo-symfony alt -->
                <img src="/images/logo.svg"
                     width="70"
                     height="70"
                     class="h-10 -mt-4 mr-4"
                     alt="Logo">
            </router-link>
        </header>
        <div class="content-wrap js-content-wrap">
            <header class="header-wrap">
                <h1 class="header-page_title">
                    <portal-target name="header-page-title" />
                </h1>
                <portal-target name="header-actions" class="header-actions" />
            </header>

            <main class="p-4">
                <!-- *** where the router component is placed *** -->
                <router-view />
            </main>
        </div>
    </div>

    <loading-spinner v-else class="mt-8" />
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import menuSubnav from './menu/subnav';
import menuSmall from './menu/small';
import menuLink from './menu/link';

export default {
    metaInfo: {
        title: 'Dashboard',
        // @todo-symfony
        titleTemplate: '%s | Symfony Starter',
    },

    components: {
        menuSubnav,
        menuSmall,
        /* eslint-disable vue/no-unused-components */
        menuLink,
    },

    data () {
        return {
            adminMenuItems: {
                'Users': 'admin-user',
            },

            copyrightYear: new Date().getFullYear(),
        };
    },

    computed: {
        ...mapState([
            'ready',
        ]),
        ...mapGetters([
            'loggedIn',
            'hasRole',
        ]),

        logoLinkRoute () {
            if (this.loggedIn) {
                return { name: 'admin-dashboard' };
            }

            return { name: 'login' };
        },
        profileLinkText () {
            if (this.loggedIn && this.$store.state.user.name) {
                return this.$store.state.user.name;
            }

            return 'Profile';
        },
    },
}
</script>
