<template>
    <AdminIcon icon="check"
               class="record_list-icon text-gray-500"
               :class="{ 'text-green-600' : active }"
               width="20"
               height="20" />
</template>

<script setup>
defineProps({
    active: {
        type: Boolean,
        required: true,
    },
});
</script>