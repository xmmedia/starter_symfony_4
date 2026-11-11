<template>
    <div class="action_menu">
        <button type="button"
                class="action_menu-button"
                :popovertarget="menuId"
                :style="{ anchorName }"
                :aria-label="label"
                :title="label"
                @pointerdown="openedByPointer = true"
                @keydown="openedByPointer = false">
            <AdminIcon icon="dots" class="action_menu-icon" />
        </button>
        <div :id="menuId"
             ref="menu"
             class="action_menu-menu"
             :style="{ positionAnchor: anchorName }"
             tabindex="-1"
             popover
             @toggle="toggled"
             @click="close"
             @keydown.down.prevent="moveFocus(1)"
             @keydown.up.prevent="moveFocus(-1)">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref, useId, useTemplateRef } from 'vue';

defineProps({
    label: {
        type: String,
        default: 'More actions',
    },
});

const menuId = useId();
const anchorName = `--${menuId}`;
const menu = useTemplateRef('menu');
const openedByPointer = ref(false);

const focusableItems = () => [...menu.value.querySelectorAll('a[href], button:not(:disabled)')];

const toggled = (e) => {
    if ('open' !== e.newState) {
        return;
    }

    // keyboard users start on the first item; with a mouse, focus the menu so the arrows still work
    if (openedByPointer.value) {
        menu.value.focus();
    } else {
        focusableItems()[0]?.focus();
    }
};

const close = (e) => {
    if (e.target.closest('a, button')) {
        menu.value.hidePopover();
    }
};

const moveFocus = (direction) => {
    const items = focusableItems();
    const current = items.indexOf(document.activeElement);

    if (-1 === current) {
        items.at(direction > 0 ? 0 : -1)?.focus();

        return;
    }

    items[(current + direction + items.length) % items.length].focus();
};
</script>
