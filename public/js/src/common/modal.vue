<template>
    <dialog ref="dialog"
            class="modal"
            @close="closed"
            @click.self="props.clickToClose ? close() : null">
        <div :class="contentClass">
            <div v-if="props.showClose" :class="closeButtonWrapClass">
                <button :class="closeButtonClass" type="button" @click="close">×</button>
            </div>

            <div class="p-6">
                <slot :close="close"></slot>
            </div>
        </div>
    </dialog>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, useTemplateRef } from 'vue';

const emit = defineEmits([ 'before-open', 'opened', 'before-close', 'closed' ]);

const props = defineProps({
    contentClass: {
        type: String,
        default: 'modal-content',
    },
    closeButtonWrapClass: {
        type: String,
        default: 'modal-close',
    },
    closeButtonClass: {
        type: String,
        default: 'button-link modal-close-button',
    },
    showClose: {
        type: Boolean,
        default: true,
    },
    clickToClose: {
        type: Boolean,
        default: true,
    },
});

const dialog = useTemplateRef('dialog');

onMounted(() => {
    emit('before-open');

    nextTick(() => {
        dialog.value.showModal();
        emit('opened');
    });
});
onBeforeUnmount(() => {
    close();
});

const close = () => {
    emit('before-close');

    if (dialog.value.requestClose) {
        dialog.value.requestClose();
    } else {
        dialog.value.close();
    }

    closed();
};

const closed = () => {
    setTimeout(() => {
        emit('closed');
    }, 1000); // time matches the CSS dialog + bg animation duration + extra buffer
};
</script>
