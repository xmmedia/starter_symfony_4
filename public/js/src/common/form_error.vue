<template>
    <ul v-if="hasErrors" class="field-errors mb-4" role="alert" aria-live="polite">
        <li><slot>Please fix the errors below and try again.</slot></li>
    </ul>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    v: {
        type: Object,
        default: null,
    },
});

const hasErrors = computed(() => {
    if (props.v) {
        return props.v.$error && props.v.$invalid;
    }

    return true;
});
</script>
