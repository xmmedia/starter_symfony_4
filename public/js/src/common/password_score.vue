<template>
    <transition appear name="password-slide">
        <!-- eslint-disable-next-line vue/require-toggle-inside-transition -->
        <div class="text-xs password-slide">
            <div class="flex items-center w-full bg-gray-500">
                <div :class="scoreBackgroundClasses" class="h-6"></div>
                <span class="absolute py-1 pl-2 text-white">
                    Strength<template v-if="passwordWarning">: {{ passwordWarning }}</template>
                </span>
            </div>
        </div>
    </transition>
</template>

<script setup>
import zxcvbn from 'zxcvbn';
import userValidation from '@/common/validation/user';
import { computed } from 'vue';

const props = defineProps({
    password: {
        type: String,
        default: undefined,
    },
    userData: {
        type: Array,
        default () {
            return [];
        },
    },
});

const result = computed(() => zxcvbn(props.password || '', userDataCompiled));
const score = computed(() => {
    if (!userValidation().password.minLength.$validator(props.password)) {
        return 0;
    }

    return result.value.score;
});
const passwordWarning = computed(() => {
    if (!result.value || !result.value.feedback.warning) {
        return undefined;
    }

    return result.value.feedback.warning;
});
const scoreBackgroundClasses = computed(() => {
    if (!props.password) {
        return 'w-0 p-1 bg-gray-500';
    }

    switch (score.value) {
        case 0 :
        case 1 :
            return 'w-1/4 p-1 bg-red-700';
        case 2 :
            return 'w-1/2 p-1 bg-yellow-600';
        case 3 :
            return 'w-3/4 p-1 bg-orange-700';
        case 4 :
            return 'w-full p-1 bg-green-700';
        default :
            return 'w-0 p-1 bg-gray-500';
    }
});
const userDataCompiled = computed(() => {
    return [
        ...props.userData,
        ...document.title.split(/[\s|]+/),
    ].filter(Boolean);
});
</script>
