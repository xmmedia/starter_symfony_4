<template>
    <ul class="flex flex-nowrap list-none" :class="{ 'header-mobile-open': showMobileMenu }">
        <li class="header-nav_item">
            <a href="/">About</a>
        </li>
        <li class="header-nav_item header-nav_item-products">
            <a href="/">Products</a>
        </li>
        <li class="header-nav_item">
            <a href="/">Contact</a>
        </li>

        <li v-if="!showMobileMenu" class="header-nav_item header-nav_item-toggle">
            <button type="button"
                    class="button-link"
                    @click="toggleMobileMenu">+ More</button>
        </li>
        <li v-else class="header-nav_item header-nav_item-toggle">
            <button type="button"
                    class="button-link"
                    @click="toggleMobileMenu">– Less</button>
        </li>
    </ul>
</template>

<script>
export default {
    data () {
        return {
            showMobileMenu: false,
        };
    },

    mounted () {
        this.$nextTick(() => {
            window.addEventListener('resize', () => { this.showMobileMenu = false });
        });
    },

    methods: {
        toggleMobileMenu () {
            this.showMobileMenu = !this.showMobileMenu;
        },
    },
}
</script>
