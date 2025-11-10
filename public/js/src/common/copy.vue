<template>
    <button :title="title"
            class="text-gray-400 focus:text-gray-600 hover:text-gray-600 hover:cursor-pointer"
            type="button"
            @click="copy">
        <component :is="iconComponent" icon="copy" :class="iconClasses" />
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
        type: [ String, Array ],
        default: 'w-4 h-4 fill-current',
    },
    iconComponent: {
        type: String,
        default: 'AdminIcon',
    },
});

const copy = () => {
    navigator.clipboard.writeText(props.text);
};
</script>
