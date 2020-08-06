<template>
    <div class="flex justify-between w-full">
        <button :class="{ 'bg-gray-800' : open }"
                class="sidebar_nav-link mb-0"
                @click.stop="toggleMenu">
            <menu-link :label="label" :icon="icon" :has-subnav="true" />
        </button>

        <div ref="submenu"
             :class="{ 'sidebar_nav-submenu-wrap-open' : open }"
             class="sidebar_nav-submenu-wrap">
            <div class="sidebar_nav-submenu_header">{{ label }}</div>
            <ul class="h-full pt-2 list-none pl-0 overflow-y-scroll">
                <li v-for="(route, anchor) in items" :key="route" class="mb-1">
                    <router-link :to="{ name: route }"
                                 class="sidebar_nav-link block py-2 px-4 hover:bg-blue-800"
                                 @click.native="subnavItemClicked">
                        {{ anchor }}
                    </router-link>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import menuLink from './link';

export default {
    components: {
        menuLink,
    },

    props: {
        label: {
            type: String,
            required: true,
        },
        items: {
            type: Object,
            required: true,
        },
        icon: {
            type: String,
            required: true,
        },
    },

    data () {
        return {
            id: Math.random().toString(36).substring(7),
            open: false,
        };
    },

    computed: {
        ...mapState('adminMenu', {
            mobileMenuIsOpen: 'mobileMenuIsOpen',
            subNavOpen: 'subNavOpen',
        }),
    },

    watch: {
        mobileMenuIsOpen (mobileMenuIsOpen) {
            if (!mobileMenuIsOpen) {
                this.close();
            }
        },
        subNavOpen (openMenuId) {
            if (openMenuId !== this.id) {
                this.close();
            }
        },
    },

    methods: {
        toggleMenu () {
            this.open = !this.open;

            if (this.open) {
                this.$store.dispatch('adminMenu/subNavOpened', this.id);
                document.documentElement.addEventListener('click', this.htmlClick);
            } else {
                this.$store.dispatch('adminMenu/subNavClosed');
                this.close();
            }
        },

        subnavItemClicked () {
            this.toggleMenu();
            this.$store.dispatch('adminMenu/closeAllMenus');
        },

        close () {
            this.open = false;
        },

        htmlClick (e) {
            if (!this.$refs.submenu.contains(e.target)) {
                this.$store.dispatch('adminMenu/subNavClosed');
                this.close();
            }
        },
    },
}
</script>
