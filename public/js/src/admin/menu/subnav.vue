<template>
    <span>
        <button class="absolute pin-r z-100 rounded-none bg-transparent hover:bg-blue-dark sidebar_nav-link sidebar_nav-submenu_arrow"
                @click.stop="toggleMenu"
                :class="{ 'sidebar_nav-submenu_arrow-open opacity-100 bg-grey-darkest' : open }">
            <svg class="w-3 h-3"><use xlink:href="#gt"></use></svg>
        </button>
        <div class="sidebar_nav-submenu-wrap"
             :class="{ 'sidebar_nav-submenu-wrap-open' : open }">
            <div class="text-2xl font-thin border-b border-grey-darker sidebar_nav-submenu_header">{{ name }}</div>
            <ul class="h-full list-reset overflow-y-scroll">
                <li v-for="(href, anchor) in items">
                    <a :href="href"
                       class="sidebar_nav-link block py-3 px-4 opacity-100 hover:bg-blue-darker">{{ anchor }}</a>
                </li>
            </ul>
        </div>
    </span>
</template>

<script>
import { mapState } from 'vuex';

export default {
    props: {
        name: {
            type: String,
            required: true,
        },
        items: {
            type: Object,
            required: true,
        }
    },

    computed: {
        ...mapState('adminMenu', {
            mobileMenuIsOpen: 'mobileMenuIsOpen',
            subNavOpen: 'subNavOpen',
        })
    },

    data() {
        return {
            id: Math.random().toString(36).substring(7),
            open: false,
        }
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

    mounted() {
        this.$nextTick(() => {
            document.documentElement.addEventListener('click', this.htmlClick);
        });
    },

    methods: {
        toggleMenu () {
            this.open = !this.open;
            if (this.open) {
                this.$store.dispatch('adminMenu/subNavOpened', this.id);
            } else {
                this.$store.dispatch('adminMenu/subNavClosed');
            }
        },
        close () {
            this.open = false;
        },
        htmlClick () {
            this.close();
            this.$store.dispatch('adminMenu/closeAllMenus');
        },
    }
}
</script>