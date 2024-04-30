<template>
    <div class="loading-wrap">
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
