<template>
    <a :href="href" class="button text-sm">Impersonate User</a>
</template>

<script setup>
const props = defineProps({
    userEmail: {
        type: String,
        required: true,
    },
});

const href = '/switch-user/redirect?_switch_user=' + encodeURIComponent(props.userEmail);
</script>
