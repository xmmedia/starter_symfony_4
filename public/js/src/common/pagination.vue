<template>
    <div v-if="pageCount > 1" class="mx-auto text-center">
        <GlobalEvents v-if="arrowEvents"
                      @keydown.left="goToPrevious"
                      @keydown.right="goToNext" />

        <component :is="isRouterMode ? RouterLink : 'button'"
                   v-if="current !== 1"
                   v-bind="isRouterMode ? { to: pageRoute(0) } : { type: 'button' }"
                   :class="linkClasses"
                   class="inline-block"
                   @click="handleNav(0)"><slot name="first-page">&lt;&lt;</slot></component>
        <span v-else :class="spanClasses" class="inline-block"><slot name="first-page">&lt;&lt;</slot></span>

        <component :is="isRouterMode ? RouterLink : 'button'"
                   v-if="previous !== null"
                   v-bind="isRouterMode ? { to: previousRoute } : { type: 'button' }"
                   :class="linkClasses"
                   class="inline-block"
                   @click="handleNav(previous - 1)"><slot name="previous-page">&lt;</slot></component>
        <span v-else :class="spanClasses" class="inline-block"><slot name="previous-page">&lt;</slot></span>

        <span v-if="showBeforeEllipsis"
              :class="ellipsisClasses"
              class="hidden md:inline-block w-5 p-1 text-gray-400">…</span>

        <template v-for="page in pagesInRange">
            <component :is="isRouterMode ? RouterLink : 'button'"
                       v-if="page !== current"
                       :key="'if-'+page"
                       v-bind="isRouterMode ? { to: pageRoute(page - 1) } : { type: 'button' }"
                       :class="linkClasses"
                       class="hidden sm:inline-block"
                       @click="handleNav(page - 1)">{{ page }}</component>
            <span v-else
                  :key="'else-'+page"
                  :class="spanClasses + ' ' + currentClasses"
                  class="hidden sm:inline-block text-gray-800 border border-gray-400">{{ page }}</span>
        </template>

        <span v-if="showAfterEllipsis"
              :class="ellipsisClasses"
              class="hidden md:inline-block w-5 p-1 text-gray-400">…</span>

        <template v-if="next !== null">
            <component :is="isRouterMode ? RouterLink : 'button'"
                       v-bind="isRouterMode ? { to: nextRoute } : { type: 'button' }"
                       :class="linkClasses"
                       class="inline-block"
                       @click="handleNav(next - 1)"><slot name="next-page">&gt;</slot></component>
            <component :is="isRouterMode ? RouterLink : 'button'"
                       v-bind="isRouterMode ? { to: lastRoute } : { type: 'button' }"
                       :class="linkClasses"
                       class="inline-block"
                       @click="handleNav(last / props.itemsPerPage)"><slot name="last-page">&gt;&gt;</slot></component>
        </template>
        <template v-else>
            <span :class="spanClasses" class="inline-block"><slot name="next-page">&gt;</slot></span>
            <span :class="spanClasses" class="inline-block"><slot name="last-page">&gt;&gt;</slot></span>
        </template>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import range from 'lodash/range';
import { GlobalEvents } from 'vue-global-events';

const router = useRouter();
const emit = defineEmits(['update:offset']);

const props = defineProps({
    routeName: {
        type: String,
        default: null,
    },
    routeQueryAdditions: {
        type: Object,
        default () {
            return {};
        },
    },
    count: {
        type: Number,
        required: true,
    },
    itemsPerPage: {
        type: Number,
        default: 30,
    },
    /**
     * The offset, not the page number.
     */
    offset: {
        type: Number,
        default: 0,
    },
    pageRange: {
        type: Number,
        default: 5,
    },
    arrowEvents: {
        type: Boolean,
        default: true,
    },
    /**
     * Additional classes to add to the active links.
     */
    linkClasses: {
        type: String,
        default: '',
    },
    /**
     * Additional classes to add to the disabled items (<span>'s).
     * Applies to: first, previous, current, next, last.
     */
    disabledClasses: {
        type: String,
        default: '',
    },
    /**
     * Additional classes to add to the current page number (<span>'s), which is disabled.
     */
    currentClasses: {
        type: String,
        default: '',
    },
    /**
     * Additional classes to add to the ellipsis (<span>'s).
     */
    ellipsisClasses: {
        type: String,
        default: null,
    },
});

const isRouterMode = computed(() => !!props.routeName);

const linkClasses = 'w-12 p-1 hover:no-underline focus:no-underline hover:bg-blue-100 rounded text-center ' + props.linkClasses;
const spanClasses = 'w-12 p-1 text-gray-400 focus:no-underline rounded ' + props.disabledClasses;

const current = computed(() => {
    if (props.offset === 0) {
        return 1;
    }
    if (props.offset > props.count) {
        return last.value / props.itemsPerPage;
    }

    return Math.ceil(props.offset / props.itemsPerPage) + 1;
});
const pageCount = computed(() => Math.ceil(props.count / props.itemsPerPage));

/**
 * Previous page number.
 */
const previous = computed(() => {
    if (current.value === 1) {
        return null;
    }

    return current.value - 1;
});
/**
 * Next page number.
 */
const next = computed(() => {
    if (current.value === pageCount.value) {
        return null;
    }

    return current.value + 1;
});
/**
 * Last page number.
 */
const last = computed(() => (pageCount.value - 1) * props.itemsPerPage);

const rangeDelta = computed(() => Math.ceil(props.pageRange / 2));
const pagesInRange = computed(() => {
    let delta = rangeDelta.value;

    // if at the end of the range
    if (current.value - delta > pageCount.value - props.pageRange) {
        let start = pageCount.value - props.pageRange + 1;
        if (start < 1) {
            start = 1;
        }

        // range doesn't include the end number
        return range(start, pageCount.value + 1);
    }

    if (current.value - delta < 0) {
        delta = current.value;
    }

    const start = current.value - delta + 1;
    let end = pageCount.value;
    if (end > props.pageRange) {
        end = props.pageRange;
    }

    // if anywhere before the end
    return range(start, start + end);
});

const showBeforeEllipsis = computed(() => pagesInRange.value && pagesInRange.value[0] > 1);
const showAfterEllipsis = computed(() => !(current.value - rangeDelta.value + 1 > pageCount.value - props.pageRange));

const previousRoute = computed(() => pageRoute(previous.value - 1));
const nextRoute = computed(() => pageRoute(next.value - 1));
const lastRoute = computed(() => pageRoute(last.value / props.itemsPerPage));

const goToPrevious = () => {
    if (previous.value) {
        if (isRouterMode.value) {
            router.push(previousRoute.value);
        } else {
            emit('update:offset', (previous.value - 1) * props.itemsPerPage);
        }
    }
};
const goToNext = () => {
    if (next.value) {
        if (isRouterMode.value) {
            router.push(nextRoute.value);
        } else {
            emit('update:offset', (next.value - 1) * props.itemsPerPage);
        }
    }
};

const handleNav = (pageIndex) => {
    if (isRouterMode.value) return;
    emit('update:offset', pageIndex * props.itemsPerPage);
};

const pageRoute = (offset) => {
    return {
        name: props.routeName,
        query: {
            ...props.routeQueryAdditions,
            offset: offset * props.itemsPerPage,
        },
    };
};
</script>
