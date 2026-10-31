<template>
    <div class="flex items-center justify-between px-4 py-2 bg-gray-300 text-sm font-medium">
        <span>Impersonating {{ name }} ({{ email }})</span>
        <a :href="'/switch-user/redirect?_switch_user=_exit&userId=' + userId"
           class="font-semibold underline text-gray-600 hover:text-gray-800">Exit Impersonation</a>
    </div>
</template>

<script setup>
defineProps({
    name: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        required: true,
    },
    userId: {
        type: String,
        required: true,
    },
});
</script>
