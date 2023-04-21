<template>
    <button ref="button"
            :title="title"
            class="text-gray-400 focus:text-gray-600 hover:text-gray-600"
            type="button">
        <AdminIcon icon="copy" class="w-4 h-4 fill-current" />
    </button>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import ClipboardJs from 'clipboard';

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: null,
    },
});

const button = ref(null);
const clipboard = new ClipboardJs(button, {
    text: () => props.text,
});

onUnmounted(() => {
    clipboard.destroy();
});
</script>
