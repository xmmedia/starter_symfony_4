<template>
    <button :title="title"
            class="text-gray-400 focus:text-gray-600 hover:text-gray-600"
            type="button"
            @click="copy">
        <AdminIcon icon="copy" :class="iconClasses" />
    </button>
</template>

<script setup>
const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: null,
    },
    iconClasses: {
        type: [String, Array],
        default: 'w-4 h-4 fill-current',
    },
});

const copy = () => {
    navigator.clipboard.writeText(props.text);
};
</script>
