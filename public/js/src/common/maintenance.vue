<template>
    <Modal v-if="maintenanceModalOpen"
           :show-close="false"
           :click-to-close="false"
           :escape-to-close="false"
           @closed="maintenanceModalOpen = false">
        <div class="max-w-md text-center">
            <div class="text-lg font-semibold">We're doing some maintenance</div>
            <p class="my-4 whitespace-pre-line">{{ maintenance.message }}</p>
            <p v-if="until" class="my-4">We expect to be back around {{ until }}.</p>
            <p class="my-4">
                Nothing you've entered has been lost. We'll carry on where you left off once we're back.
            </p>
            <p v-if="stillDown" class="my-4 font-semibold">We're not back yet.</p>
            <div class="mt-8">
                <button type="button" class="button" @click="check">Check now</button>
            </div>
        </div>
    </Modal>

    <!-- shown if they dismiss the modal -->
    <div v-else-if="maintenance"
         class="alert alert-warning fixed inset-x-4 bottom-4 z-50 items-center gap-x-4 text-gray-900 shadow-lg"
         role="alert">
        <div>We're doing some maintenance. We'll carry on where you left off once we're back.</div>
        <div class="flex items-center gap-x-4 shrink-0">
            <button class="button-link" type="button" @click="check">Check now</button>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { format as formatDate, isToday } from 'date-fns';
import Modal from '@/common/modal.vue';
import { endMaintenance, maintenance, maintenanceModalOpen } from '@/common/maintenance';
import { fetchSessionInfo } from '@/common/session';

const CHECK_INTERVAL = 120;

const stillDown = ref(false);

const until = computed(() => {
    if (!maintenance.value?.until) {
        return null;
    }

    const date = new Date(maintenance.value.until);
    if (isToday(date)) {
        return formatDate(date, 'h:mm aaa');
    }

    return formatDate(date, 'MMM d \'at\' h:mm aaa');
});

let checkTimer = null;

/**
 * Any request is answered with the maintenance response while it's on. /session-info is used
 * as it's light & works whether they're signed in or not.
 */
const check = async () => {
    try {
        await fetchSessionInfo();
    } catch {
        // still in maintenance, or the server or their connection is down: try again later
        stillDown.value = true;

        return;
    }

    endMaintenance();
};

watch(maintenance, (details) => {
    clearInterval(checkTimer);

    if (null === details) {
        stillDown.value = false;

        return;
    }

    checkTimer = setInterval(() => {
        if ('visible' === document.visibilityState) {
            check();
        }
    }, CHECK_INTERVAL * 1000);
});

// check when they come back to the window, as the timer skips while it's hidden & pauses while
// the computer's asleep
const returned = () => {
    if (maintenance.value && 'visible' === document.visibilityState) {
        check();
    }
};

onMounted(() => {
    document.addEventListener('visibilitychange', returned);
});
onBeforeUnmount(() => {
    clearInterval(checkTimer);
    document.removeEventListener('visibilitychange', returned);
});
</script>
