<template>
    <ul v-if="hasErrors"
        class="field-errors"
        role="alert"
        aria-live="polite">
        <li><slot></slot></li>
    </ul>
</template>

<script>
export default {
    computed: {
        hasErrors () {
            return !!this.$slots.default;
        },
    },
}
</script>
