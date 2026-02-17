<template>
    <div class="bg-amber-400 text-amber-900 text-sm font-medium px-4 py-2 flex items-center justify-between">
        <span>Impersonating {{ name }} ({{ email }})</span>
        <a :href="'/switch-user/redirect?_switch_user=_exit&userId=' + userId"
           class="underline font-semibold text-amber-700 hover:text-amber-900">Exit Impersonation</a>
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
