<template>
    <!-- !! Note: !! -->
    <!-- Using this slot and replacing the whole button will result in the refocusing of the button not working -->
    <slot name="button" :open="open">
        <button ref="link"
                :disabled="disabled"
                :class="linkClasses"
                :title="buttonTitle"
                type="button"
                @click="open"><slot name="button-text">Delete</slot></button>
    </slot>

    <ConfirmModal v-if="show"
                  critical
                  :working="deleting"
                  @confirm="deleteRecord"
                  @closed="closed">
        <slot>
            Are you sure you want to delete this {{ recordDesc }}?
            <br>This cannot be undone.
        </slot>

        <template #additional><slot name="additional" /></template>
        <template #confirm-text><slot name="button-text">Delete</slot></template>
        <template #cancel-text><slot name="cancel-text">Cancel</slot></template>
        <template #working>Deleting {{ recordDesc }}…</template>
    </ConfirmModal>
</template>

<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import ConfirmModal from '@/common/confirm_modal.vue';

const emit = defineEmits([ 'delete' ]);

defineProps({
    recordDesc: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    linkClasses: {
        type: String,
        default: 'button-link form-action',
    },
    buttonTitle: {
        type: String,
        default: null,
    },
});

const show = ref(false);
const deleting = ref(false);

const link = ref(null);

const open = () => {
    show.value = true;
};

onBeforeUnmount(() => {
    removeResizeListener();
});

watch(show, (newVal) => {
    if (newVal) {
        window.addEventListener('resize', resize, { once: true });
    } else {
        removeResizeListener();
    }
});

const resize = () => {
    show.value = false;
};
const removeResizeListener = () => {
    window.removeEventListener('resize', resize, { once: true });
};

const closed = () => {
    show.value = false;
    // reset everything else
    deleting.value = false;
    if (link.value) {
        link.value.focus();
    }
    removeResizeListener();
};

const deleteRecord = () => {
    deleting.value = true;
    setTimeout(() => {
        emit('delete');
    }, 1000);
};
</script>
