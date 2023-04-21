<template>
    <button class="button-link text-white -mt-4 ml-4 hover:text-white focus:ring-offset-gray-900"
            @click.stop="toggleMenu">Menu</button>
</template>

<script setup>
import { computed, nextTick, onMounted } from 'vue';
import { useMenuStore } from '@/admin/stores/menu';

const menuStore = useMenuStore();

const open = computed(() => menuStore.mobileMenuIsOpen);

onMounted(() => {
    nextTick(() => {
        setContentHeight();
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

    setContentHeight();
}

function setContentHeight () {
    document.querySelectorAll('.js-content-wrap')[0].style.minHeight = getWindowHeight() + 'px';
}

function getWindowHeight () {
    let d = document,
        e = d.documentElement,
        g = d.body;

    return window.innerHeight || e.clientHeight || g.clientHeight;
}

function htmlClick () {
    menuStore.closeMobileMenu();
    document.documentElement.removeEventListener('click', htmlClick);
}
</script>
