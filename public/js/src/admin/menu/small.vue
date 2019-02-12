<template>
    <button class="button-link text-white ml-4"
            style="margin-top: -1rem;"
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
    },
}
</script>
