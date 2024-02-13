<template>
    <template v-if="!props.user.active">Inactive</template>
    <template v-else-if="!props.user.verified">Not Verified</template>
    <template v-else>Active</template>
</template>

<script setup>
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});
</script>
