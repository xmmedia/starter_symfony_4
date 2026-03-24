<template>
    {{ label }}
</template>

<script setup>
const labels = {
    LOGIN:                 'Successful Login',
    LOGIN_FAILED:          'Failed Login',
    IMPERSONATION_STARTED: 'Impersonation Started',
    IMPERSONATION_ENDED:   'Impersonation Ended',
};

const props = defineProps({
    eventType: {
        type: String,
        required: true,
    },
});

const label = labels[props.eventType] ?? props.eventType;
</script>
