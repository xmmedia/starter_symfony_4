<template>
    <ul v-if="suggestions.length > 0"
        :id="listId"
        class="absolute z-10 w-full mt-1 pl-0 list-none bg-white border border-gray-300 shadow-lg"
        role="listbox">
        <li v-for="(suggestion, index) in suggestions"
            :id="`${listId}-${index}`"
            :key="suggestion.placePrediction.placeId"
            :class="['px-3 py-2 text-sm cursor-pointer', { 'bg-gray-100': index === activeIndex }]"
            role="option"
            :aria-selected="index === activeIndex"
            @mousedown.prevent="$emit('select', suggestion)"
            @mouseover="$emit('activate', index)">
            <span>{{ suggestion.placePrediction.mainText?.toString() }}</span>
            <span class="ml-1 text-gray-600">{{ suggestion.placePrediction.secondaryText?.toString() }}</span>
        </li>
        <li class="px-3 py-1 border-t border-gray-200">
            <img src="https://maps.gstatic.com/mapfiles/api-3/images/powered-by-google-on-white3.png"
                 width="144"
                 height="18"
                 alt="Powered by Google">
        </li>
    </ul>
</template>

<script setup>
defineEmits([ 'select', 'activate' ]);

defineProps({
    listId: {
        type: String,
        required: true,
    },
    suggestions: {
        type: Array,
        required: true,
    },
    activeIndex: {
        type: Number,
        required: true,
    },
});
</script>
