<template>
    <span class="flex justify-between items-center w-full mb-1 py-2 pr-2 pl-4 font-light">
        <span class="flex flex-wrap justify-start items-center w-full lg:text-base">
            <AdminIcon :icon="icon"
                       class="size-5 mr-3"
                       width="20"
                       height="20" />
            <span class="w-2/3 lg:w-4/5 leading-snug">{{ label }}</span>
        </span>
        <AdminIcon v-if="hasSubnav"
                   icon="gt"
                   class="size-3"
                   width="12"
                   height="12" />
    </span>
</template>

<script setup>
defineProps({
    icon: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    hasSubnav: {
        type: Boolean,
        default: false,
    },
});
</script>
