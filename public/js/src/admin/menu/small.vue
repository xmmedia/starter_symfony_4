<template>
    <button class="button-link text-white -mt-4 ml-4 hover:text-white focus:ring-offset-gray-900"
            @click.stop="toggleMenu">Menu</button>
</template>

<script setup>
import { computed, nextTick, onMounted } from 'vue';
import { useStore } from 'vuex';

const store = useStore();

const open = computed(() => store.state.adminMenu.mobileMenuIsOpen);

onMounted(() => {
    nextTick(() => {
        setContentHeight();
        window.addEventListener('resize', windowResize);
    });
});

function toggleMenu () {
    if (open.value) {
        store.dispatch('adminMenu/closeMobileMenu');
    } else {
        store.dispatch('adminMenu/openMobileMenu');
        document.documentElement.addEventListener('click', htmlClick);
    }
}

function windowResize () {
    store.dispatch('adminMenu/closeMobileMenu');

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
    store.dispatch('adminMenu/closeMobileMenu');
    document.documentElement.removeEventListener('click', htmlClick);
}
</script>
