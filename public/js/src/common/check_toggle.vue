<template>
    <button ref="button"
            type="button"
            class="flex items-center gap-x-2 w-fit text-left disabled:cursor-wait"
            :disabled="saving"
            :aria-label="label"
            :aria-busy="saving"
            :title="label"
            @click="clicked">
        <AdminIconCheck :active="checked" :class="{ 'animate-pulse': saving }" />
        <span v-if="checkedText || uncheckedText">{{ saving ? 'Saving…' : text }}</span>
    </button>

    <ConfirmModal v-if="confirming"
                  :confirm-text="resolve(confirmButtonText, !checked)"
                  :critical="resolve(critical, !checked)"
                  @confirm="confirmed"
                  @closed="confirmClosed">
        <slot name="confirm" :checked="!checked">{{ resolve(confirm, !checked) }}</slot>
    </ConfirmModal>
</template>

<script setup>
import { computed, nextTick, ref, useTemplateRef } from 'vue';
import { logError } from '@/common/lib';
import ConfirmModal from '@/common/confirm_modal.vue';

const checked = defineModel('checked', {
    type: Boolean,
    required: true,
});

const props = defineProps({
    /** What clicking does, for screen readers & the tooltip, eg "Deactivate example.com". */
    label: {
        type: String,
        required: true,
    },
    /** Saves the new value: `async (checked) => {}`. Throw if it fails, & the value isn't changed. */
    save: {
        type: Function,
        required: true,
    },
    /** Text shown next to the icon, when it's checked. Leave both texts out for the icon alone. */
    checkedText: {
        type: String,
        default: null,
    },
    uncheckedText: {
        type: String,
        default: null,
    },
    /**
     * The confirmation shown in a modal before it's saved: a string, or `(checked) => string|null`, given the new
     * value, for a confirmation one way only (null skips it). The `confirm` slot replaces the text.
     */
    confirm: {
        type: [ String, Function ],
        default: null,
    },
    /** The modal's confirm button: a string, or `(checked) => string`. */
    confirmButtonText: {
        type: [ String, Function ],
        default: 'Confirm',
    },
    /**
     * For a scary change: the confirm button is red & Cancel is focused, so Enter cancels. A boolean, or
     * `(checked) => boolean`.
     */
    critical: {
        type: [ Boolean, Function ],
        default: false,
    },
    errorMessage: {
        type: String,
        default: 'There was a problem saving the change. Please try again later.',
    },
});

const button = useTemplateRef('button');
const saving = ref(false);
const confirming = ref(false);
let fromKeyboard = false;

const text = computed(() => (checked.value ? props.checkedText : props.uncheckedText));

const resolve = (value, newChecked) => ('function' === typeof value ? value(newChecked) : value);

const clicked = (event) => {
    if (saving.value) {
        return;
    }

    // a click from Enter or Space has no click count, so the ring is only shown again when refocusing for the keyboard
    fromKeyboard = 0 === event.detail;

    if (resolve(props.confirm, !checked.value)) {
        confirming.value = true;

        return;
    }

    toggle();
};

const confirmed = (close) => {
    close();
    toggle();
};

// the modal's closed after its animation, so the save may still be running: it refocuses the button when it's done
const confirmClosed = () => {
    confirming.value = false;
    if (!saving.value) {
        refocus();
    }
};

// shows the ring after the keyboard, & otherwise leaves it to the browser: false would hide it after Escape, say
const refocus = () => {
    button.value?.focus(fromKeyboard ? { focusVisible: true } : {});
};

const toggle = async () => {
    saving.value = true;
    const newChecked = !checked.value;

    try {
        await props.save(newChecked);

        checked.value = newChecked;
    } catch (e) {
        logError(e);
        alert(props.errorMessage);
    } finally {
        saving.value = false;
    }

    if (confirming.value) {
        return;
    }

    await nextTick();
    refocus();
};
</script>
