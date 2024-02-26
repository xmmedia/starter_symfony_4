<template>
    <VueFinalModal v-model="show"
                   :click-to-close="props.clickToClose"
                   class="flex justify-center items-center"
                   content-class="admin-modal-content"
                   overlay-transition="vfm-fade"
                   content-transition="vfm-fade"
                   @before-open="$emit('before-open', $event)"
                   @opened="$emit('opened')"
                   @before-close="$emit('before-close', $event)"
                   @closed="$emit('closed')">
        <div class="relative">
            <div v-if="props.showClose" class="absolute top-0 right-0 text-4xl leading-3">
                <button class="text-slate-600 hover:text-slate-300 transition-colors duration-300"
                        type="button"
                        @click="close">×</button>
            </div>

            <div class="p-6">
                <slot :close="close"></slot>
            </div>
        </div>
    </VueFinalModal>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { VueFinalModal } from 'vue-final-modal';

const props = defineProps({
    showClose: {
        type: Boolean,
        default: true,
    },
    clickToClose: {
        type: Boolean,
        default: true,
    },
});

defineEmits(['before-open', 'opened', 'before-close', 'closed']);

const show = ref(false);

onMounted(() => {
    nextTick(() => {
        show.value = true;
    });
});
onBeforeUnmount(() => {
    show.value = false;
});

function close () {
    show.value = false;
}
</script>
