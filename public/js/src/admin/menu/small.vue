<template>
    <button class="button-link -mt-4 ml-4 focus:ring-offset-gray-900 text-white hover:text-white focus:text-white"
            @click.stop="toggleMenu">Menu</button>
</template>

<script setup>
import { computed, nextTick, onMounted } from 'vue';
import { useMenuStore } from '@/admin/stores/menu';

const menuStore = useMenuStore();

const open = computed(() => menuStore.mobileMenuIsOpen);

onMounted(() => {
    nextTick(() => {
        window.addEventListener('resize', windowResize);
    });
});

const toggleMenu = () => {
    if (open.value) {
        menuStore.closeMobileMenu();
    } else {
        menuStore.openMobileMenu();
        document.documentElement.addEventListener('click', htmlClick);
    }
};

const windowResize = () => {
    menuStore.closeMobileMenu();
};

const htmlClick = () => {
    menuStore.closeMobileMenu();
    document.documentElement.removeEventListener('click', htmlClick);
};
</script>
