<template>
    <div class="flex justify-center items-center">
        <div class="loading" :class="spinnerClasses" aria-hidden="true" />
        <slot></slot>
    </div>
</template>

<script setup>
defineProps({
    spinnerClasses: {
        type: String,
        default: null,
    },
});
</script>
