<template>
    <span class="flex justify-between items-center w-full py-2 pr-2 pl-4 mb-1 font-light">
        <span class="flex flex-wrap justify-start items-center w-full lg:text-base">
            <AdminIcon :icon="icon"
                       class="w-5 h-5 mr-3 mb-0"
                       width="20"
                       height="20" />
            <span class="leading-none">{{ label }}</span>
        </span>
        <AdminIcon v-if="hasSubnav"
                   icon="gt"
                   class="w-3 h-3"
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
