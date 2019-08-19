<template>
    <div v-if="ready">
        <svg-icons :src="iconsPath" />

        <nav class="sidebar_nav-wrap">
            <router-link :to="logoLinkRoute"
                         class="flex items-center w-40 lg:w-64 border-b border-gray-600"
                         style="height: 3.75rem; padding: 0.65rem 0;">
                <!-- @todo-symfony alt -->
                <img src="/images/logo.svg" width="80" alt="Logo">
            </router-link>

            <ul class="sidebar_nav-nav">
                <template v-if="loggedIn">
                    <!--<li class="hover:bg-gray-800">
                        <router-link :to="{ name: 'admin-dashboard' }"
                                     class="sidebar_nav-link">
                            <menu-link label="Name" icon="users" />
                        </router-link>
                    </li>-->

                    <li v-if="hasRole('ROLE_ADMIN')" class="hover:bg-gray-800">
                        <menu-subnav :items="adminMenuItems" label="Admin" icon="gear" />
                    </li>
                </template>
            </ul>

            <div class="absolute bottom-0 w-40 lg:w-64 pt-2 text-gray-500 font-thin">
                <div class="flex items-end justify-between px-4">
                    <div v-if="loggedIn" class="w-3/5 lg:w-2/3 mb-2 text-sm" style="overflow-wrap:break-word;">
                        <router-link :to="{ name: 'user-profile-edit' }"
                                     class="text-inherit hover:no-underline hover:text-white">
                            {{ profileLinkText }}
                        </router-link>
                    </div>
                    <div v-if="loggedIn" class="pb-2 pl-4 text-xs">
                        <a href="/logout" class="text-inherit hover:no-underline hover:text-white">Logout</a>
                    </div>
                </div>
                <div class="py-2 pl-4 text-xs text-gray-600 border-t border-gray-600">
                    <!-- @todo-symfony -->
                    ©{{ copyrightYear }} XM Media Inc.
                </div>
            </div>
        </nav>

        <header class="header-wrap-small">
            <menu-small />
            <router-link :to="logoLinkRoute">
                <img src="/images/logo.svg" width="80" class="w-8 -mt-4 mr-4" alt="Logo">
            </router-link>
        </header>
        <div class="content-wrap js-content-wrap">
            <header class="header-wrap">
                <h1 class="header-page_title font-thin">
                    <portal-target name="header-page-title" />
                </h1>
                <portal-target name="header-actions" class="header-actions" />
            </header>

            <main class="p-4">
                <!-- *** where the router component is placed *** -->
                <router-view />
            </main>
        </div>

        <portal-target name="modal" multiple />
    </div>

    <loading-spinner v-else class="mt-8" />
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import menuSubnav from './menu/subnav';
import menuSmall from './menu/small';
import menuLink from './menu/link';
import svgIcons from '@/common/svg_icons';

import iconsPath from '../../../images/icons-admin.svg';

export default {
    components: {
        menuSubnav,
        menuSmall,
        menuLink,
        svgIcons,
    },

    data () {
        return {
            iconsPath,

            adminMenuItems: {
                'Users': 'admin-user',
            },

            copyrightYear: (new Date()).getFullYear(),
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
