<template>
    <button class="button-link text-white -mt-4 ml-4 hover:text-white focus:text-white focus:ring-offset-gray-900"
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

function toggleMenu () {
    if (open.value) {
        menuStore.closeMobileMenu();
    } else {
        menuStore.openMobileMenu();
        document.documentElement.addEventListener('click', htmlClick);
    }
}

function windowResize () {
    menuStore.closeMobileMenu();
}

function htmlClick () {
    menuStore.closeMobileMenu();
    document.documentElement.removeEventListener('click', htmlClick);
}
</script>
