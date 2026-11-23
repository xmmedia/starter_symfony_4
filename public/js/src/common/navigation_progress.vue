<template>
    <!-- main has aria-busy while navigating, so this is only visual -->
    <div ref="bar"
         class="navigation_progress"
         :class="{
             'navigation_progress--loading': state.matches('loading'),
             'navigation_progress--done': state.matches('done'),
         }"
         aria-hidden="true" />
</template>

<script setup>
import { nextTick, useTemplateRef, watch } from 'vue';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { navigating } from '@/common/router_helpers';

const stateMachine = createMachine({
    id: 'component',
    initial: 'hidden',
    states: {
        hidden: {
            on: {
                START: 'loading',
            },
        },
        loading: {
            on: {
                DONE: 'done',
            },
        },
        done: {
            on: {
                RESET: 'hidden',
            },
            // once it's filled & faded out (navigation_progress.css)
            after: {
                350: 'hidden',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const bar = useTemplateRef('bar');

watch(navigating, async (isNavigating) => {
    if (!isNavigating) {
        sendEvent({ type: 'DONE' });

        return;
    }

    // start from empty, even if the last one is still fading out
    sendEvent({ type: 'RESET' });
    await nextTick();
    // it finished while waiting
    if (!navigating.value) {
        return;
    }

    // force a reflow so it transitions from empty
    void bar.value.offsetWidth;
    sendEvent({ type: 'START' });
});
</script>
