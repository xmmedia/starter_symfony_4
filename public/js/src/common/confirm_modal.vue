<template>
    <AdminModal @opened="opened" @closed="emit('closed')">
        <template #default="{ close }">
            <LoadingSpinner v-if="working" class="p-12 text-center">
                <slot name="working" />
            </LoadingSpinner>

            <div v-else class="text-center">
                <div class="my-4">
                    <slot />
                </div>
                <slot name="additional" />
                <div class="mt-8">
                    <button ref="confirmButton"
                            :class="confirmButtonClasses"
                            type="button"
                            @click="emit('confirm', close)"><slot name="confirm-text">{{ confirmText }}</slot></button>
                    <button ref="cancelButton"
                            class="form-action button-link"
                            type="button"
                            @click="close"><slot name="cancel-text">{{ cancelText }}</slot></button>
                </div>
            </div>
        </template>
    </AdminModal>
</template>

<script setup>
import { computed, useTemplateRef } from 'vue';

/**
 * Emits `confirm` with the modal's `close()`: the modal stays open until it's called (eg to show `working`). Cancel,
 * Escape & the backdrop close it without confirming. Show it with `v-if` & hide it on `closed`.
 */
const emit = defineEmits([ 'confirm', 'closed' ]);

const props = defineProps({
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    /**
     * For a scary action (eg a delete): the confirm button is red & Cancel is focused, so Enter cancels. Otherwise the
     * confirm button is focused.
     */
    critical: {
        type: Boolean,
        default: false,
    },
    /** Shows a spinner, with the `working` slot, in place of the message & buttons. */
    working: {
        type: Boolean,
        default: false,
    },
});

const confirmButton = useTemplateRef('confirmButton');
const cancelButton = useTemplateRef('cancelButton');

const confirmButtonClasses = computed(() => {
    if (props.critical) {
        return 'button button-critical button-critical-solid';
    }

    return 'button';
});

// focus moved by code after a click doesn't match :focus-visible, so the button wouldn't show
// it's the one Enter presses
const opened = () => {
    (props.critical ? cancelButton : confirmButton).value?.focus({ focusVisible: true });
};
</script>
