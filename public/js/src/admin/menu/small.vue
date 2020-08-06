<template>
    <button class="button-link text-white -mt-4 ml-4 focus:-mx-1 focus:ml-3 focus:px-1 focus:bg-gray-700"
            @click.stop="toggleMenu">Menu</button>
</template>

<script>
import { mapState } from 'vuex';

export default {
    computed: {
        ...mapState('adminMenu', {
            open: 'mobileMenuIsOpen',
        }),
    },

    mounted() {
        this.$nextTick(() => {
            this.setContentHeight();
            window.addEventListener('resize', this.windowResize);
        });
    },

    methods: {
        toggleMenu () {
            if (this.open) {
                this.$store.dispatch('adminMenu/closeMobileMenu');
            } else {
                this.$store.dispatch('adminMenu/openMobileMenu');
                document.documentElement.addEventListener('click', this.htmlClick);
            }
        },
        windowResize() {
            this.$store.dispatch('adminMenu/closeMobileMenu');

            this.setContentHeight();
        },
        setContentHeight() {
            document.querySelectorAll('.js-content-wrap')[0].style.minHeight = this.getWindowHeight() + 'px';
        },
        getWindowHeight() {
            let d = document,
                e = d.documentElement,
                g = d.body;

            return window.innerHeight || e.clientHeight || g.clientHeight;
        },

        htmlClick () {
            this.$store.dispatch('adminMenu/closeMobileMenu');
            document.documentElement.removeEventListener('click', this.htmlClick);
        },
    },
}
</script>
