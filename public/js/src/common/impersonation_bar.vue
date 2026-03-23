<template>
    <div class="bg-gray-300 text-gray-800 text-sm font-medium px-4 py-2 flex items-center justify-between">
        <span>Impersonating {{ name }} ({{ email }})</span>
        <a :href="'/switch-user/redirect?_switch_user=_exit&userId=' + userId"
           class="underline font-semibold text-gray-600 hover:text-gray-800">Exit Impersonation</a>
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
