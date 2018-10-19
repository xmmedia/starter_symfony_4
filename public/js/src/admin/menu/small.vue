<template>
    <a href="" @click.stop.prevent="toggleMenu" class="ml-4">Menu</a>
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
            document.querySelectorAll('.js-content-wrap')[0]
                .style.minHeight = this.getWindowHeight() + 'px';
        },
        getWindowHeight() {
            let d = document,
                e = d.documentElement,
                g = d.body;

            return window.innerHeight || e.clientHeight || g.clientHeight;
        }
    }
}
</script>