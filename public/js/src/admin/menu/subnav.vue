<template>
    <div class="flex justify-between w-full">
        <button :class="{ 'bg-gray-800' : open }"
                class="button-link sidebar_nav-link mb-0"
                @click.stop="toggleMenu">
            <MenuLink :label="label" :icon="icon" :has-subnav="true" />
        </button>

        <div ref="submenu"
             :class="{ 'sidebar_nav-submenu-wrap-open' : open }"
             class="sidebar_nav-submenu-wrap">
            <div class="sidebar_nav-submenu_header">{{ label }}</div>
            <ul class="h-full pt-2 list-none pl-0 overflow-y-scroll">
                <li v-for="(route, anchor) in items" :key="route" class="mb-1">
                    <RouterLink :to="{ name: route }"
                                class="sidebar_nav-link block py-2 px-4 hover:bg-blue-800
                                       ring-offset-gray-800 focus:no-underline"
                                @click="subnavItemClicked">
                        {{ anchor }}
                    </RouterLink>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useMenuStore } from '@/admin/stores/menu';
import cuid from 'cuid';
import MenuLink from './link';

const menuStore = useMenuStore();

defineProps({
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
});

const open = ref(false);
const submenu = ref(null);

watch(computed(() => menuStore.mobileMenuIsOpen), (mobileMenuIsOpen) => {
    if (!mobileMenuIsOpen) {
        close();
    }
});
watch(computed(() => menuStore.subNavOpen), (subNavOpen) => {
    if (!subNavOpen) {
        close();
    }
});

function toggleMenu () {
    open.value = !open.value;

    if (open.value) {
        menuStore.subNavOpened(cuid());
        document.documentElement.addEventListener('click', htmlClick);
    } else {
        menuStore.subNavClosed();
        close();
    }
}

function subnavItemClicked () {
    toggleMenu();
    menuStore.closeAllMenus();
}

function close () {
    open.value = false;
}

function htmlClick (e) {
    if (!submenu.value.contains(e.target)) {
        menuStore.subNavClosed();
        close();
    }
}
</script>
