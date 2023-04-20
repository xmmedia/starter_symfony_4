<template>
    <time :datetime="datetime">
        <span class="whitespace-nowrap">{{ displayDate }}</span>
        <span class="whitespace-nowrap">{{ displayTime }}</span>
    </time>
</template>

<script setup>
import { format as formatDate } from 'date-fns';
import { computed } from 'vue';

const props = defineProps({
    datetime: {
        type: [String, Date],
        required: true,
    },
});

const date = computed(() => {
    // just in case it's a string
    return new Date(props.datetime);
});
const displayDate = computed(() => formatDate(date.value, 'yyyy-MM-dd'));
const displayTime = computed(() => formatDate(date.value, 'HH:mm'));
</script>
