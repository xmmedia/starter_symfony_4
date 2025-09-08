<template>
    <dialog ref="dialog"
            class="admin-modal"
            @close="closed"
            @click.self="props.clickToClose ? close() : null">
        <div :class="contentClass">
            <div v-if="props.showClose" class="absolute top-4 right-5 text-2xl leading-3">
                <button class="button-link pb-1 text-slate-600 hover:text-slate-400
                           focus:ring-offset-2 ring-offset-gray-800 focus:text-slate-350 hover:text-slate-350 no-underline"
                        type="button"
                        @click="close">×
                </button>
            </div>

            <div class="p-6">
                <slot :close="close"></slot>
            </div>
        </div>
    </dialog>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, useTemplateRef } from 'vue';

const props = defineProps({
    contentClass: {
        type: String,
        default: 'admin-modal-content',
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

const emit = defineEmits([ 'before-open', 'opened', 'before-close', 'closed' ]);

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
    console.log('closed');

    setTimeout(() => {
        emit('closed');
    }, 1000); // time matches the CSS dialog + bg animation duration + extra buffer
};
</script>
