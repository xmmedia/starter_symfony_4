<template>
    <div v-if="ready">
        <svg-icons :src="iconsPath" />

        <nav class="sidebar_nav-wrap">
            <router-link :to="{ name: 'admin-dashboard' }"
                         class="flex items-center justify-center w-24 border-b border-grey-darker"
                         style="height: 3.75rem; padding: 0.65rem 0;">
                <img src="/images/logo.svg"
                     width="80"
                     class="block my-0 mx-auto"
                     style="max-width: 5rem; max-height: 2.3rem;"
                     alt="Battery Analytics">
            </router-link>
            <ul class="sidebar_nav-nav">
                <template v-if="loggedIn">
                    <li>
                        <router-link :to="{ name: 'admin-dashboard' }"
                                     class="sidebar_nav-link sidebar_nav-nav_item">
                            <svg><use xlink:href="#gear"></use></svg>
                            Admin
                        </router-link>

                        <menu-subnav :items="adminMenuItems" name="Admin" />
                    </li>
                </template>
            </ul>

            <div class="sidebar_nav-bottom_wrap">
                <div v-if="loggedIn" class="text-sm">
                    <router-link :to="{ name: 'user-profile-edit' }"
                                 class="text-inherit">
                        {{ profileLinkText }}
                    </router-link>
                </div>
                <div class="p-1 text-xs">
                    <a href="/logout" class="text-inherit">Logout</a>
                </div>
                <div class="py-2 text-xs text-grey-darker border-t border-grey-darker">
                    <!-- @todo-symfony -->
                    ©{{ copyrightYear }} XM Media Inc.
                </div>
            </div>
        </nav>

        <header class="header-wrap-small">
            <menu-small />
            <router-link :to="{ name: 'admin-dashboard' }" class="mr-4" style="width: 2rem;">
                <img src="/images/logo.svg" width="80" alt="Battery Analytics">
            </router-link>
        </header>
        <div class="content-wrap js-content-wrap">
            <header class="header-wrap">
                <portal-target name="header-page-title" class="header-page_title" />
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
import svgIcons from '@/common/svg_icons';

import iconsPath from '../../../images/icons-admin.svg';

export default {
    components: {
        'svg-icons': svgIcons,
        'menu-subnav': menuSubnav,
        'menu-small': menuSmall,
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
        ]),

        profileLinkText () {
            if (this.loggedIn && this.$store.state.user.name) {
                return this.$store.state.user.name;
            }

            return 'Profile';
        },
    },
}
</script>
