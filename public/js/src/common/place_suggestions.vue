<template>
    <ul v-if="suggestions.length > 0"
        :id="listId"
        class="absolute z-10 w-full mt-1 pl-0 list-none bg-white border border-gray-300 shadow-lg"
        role="listbox">
        <li v-for="(suggestion, index) in suggestions"
            :id="`${listId}-${index}`"
            :key="suggestion.placePrediction.placeId"
            :class="[ 'px-3 py-2 text-sm cursor-pointer', { 'bg-gray-100': index === activeIndex } ]"
            role="option"
            :aria-selected="index === activeIndex"
            @mousedown.prevent="$emit('select', suggestion)"
            @mouseover="$emit('activate', index)">
            <span>{{ suggestion.placePrediction.mainText?.toString() }}</span>
            <span class="ml-1 text-gray-600">{{ suggestion.placePrediction.secondaryText?.toString() }}</span>
        </li>
        <!-- attribution required by the Places policies -->
        <!-- @link https://developers.google.com/maps/documentation/places/web-service/policies -->
        <!-- the logo can't be modified & needs clear space: 10px left, right & top, 5px bottom -->
        <li class="px-2.5 pt-2.5 pb-1.25 border-t border-gray-200">
            <img src="/images/google-maps-logo-gray.svg"
                 class="block h-4 w-auto ml-auto"
                 width="87"
                 height="16"
                 alt="Google Maps">
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
