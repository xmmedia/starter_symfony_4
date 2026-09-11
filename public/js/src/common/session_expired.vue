<template>
    <!-- hidden while the maintenance modal's showing -->
    <template v-if="!maintenance">
        <Modal v-if="sessionModalOpen"
               :show-close="false"
               :click-to-close="false"
               :escape-to-close="false"
               @closed="sessionModalOpen = false">
            <div class="max-w-md text-center">
                <div class="text-lg font-semibold">You've been signed out</div>
                <p class="my-4">
                    Your session has ended, either because it expired or you signed out in another window.
                    Sign in again in a new tab, then come back here to carry on where you left off.
                </p>
                <p v-if="stillSignedOut" class="my-4 font-semibold">You're still signed out.</p>
                <div class="mt-8">
                    <a :href="loginUrl" target="_blank" class="button">Sign in</a>
                    <button class="form-action button-link" type="button" @click="checkSession">Continue</button>
                </div>
            </div>
        </Modal>

        <!-- shown if they dismiss the modal while still signed out -->
        <div v-else-if="sessionExpired"
             class="alert alert-warning fixed inset-x-4 bottom-4 z-50 items-center gap-x-4 text-gray-900 shadow-lg"
             role="alert">
            <div>You've been signed out. Sign in again to continue.</div>
            <div class="flex items-center gap-x-4 shrink-0">
                <a :href="loginUrl" target="_blank" class="button">Sign in</a>
                <button class="button-link" type="button" @click="checkSession">Continue</button>
            </div>
        </div>

        <Modal v-else-if="showWarning" @closed="warningDismissed = null !== expiresAt">
            <div class="max-w-md text-center">
                <div class="text-lg font-semibold">Your session is about to expire</div>
                <p class="my-4">You'll be signed out in {{ countdown }}.</p>
                <div class="mt-8">
                    <button type="button" class="button" @click="keepSignedIn">Keep me signed in</button>
                    <a href="/logout" class="form-action button-link">Sign out now</a>
                </div>
            </div>
        </Modal>
    </template>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { logError } from '@/common/lib';
import Modal from '@/common/modal.vue';
import { MaintenanceError, maintenance } from '@/common/maintenance';
import {
    expireSession,
    extendSession,
    fetchSessionInfo,
    resumeSession,
    sessionExpired,
    sessionModalOpen,
    useSessionStore,
} from '@/common/session';

// how long before the session expires to warn them
const WARNING_SECONDS = 120;
// timers drift & pause while the computer's asleep, so don't wait too long between checks
const MAX_CHECK_INTERVAL = 60 * 60;
const RETRY_INTERVAL = 60;
// when they come back to the window, only check if it's been this long. Otherwise their next
// request finds out they've been signed out (it's held & the modal opened)
const RETURN_CHECK_INTERVAL = 5 * 60;

const rootStore = useSessionStore();

const stillSignedOut = ref(false);
// the sign in page skips to the password step when it's given the email.
// Not when impersonating: they need to sign in as themselves.
const loginUrl = computed(() => {
    if (!rootStore.loggedIn || rootStore.user.isImpersonating) {
        return '/login';
    }

    return '/login?' + new URLSearchParams({ email: rootStore.user.email });
});

// set while warning them, in ms
const expiresAt = ref(null);
const now = ref(Date.now());
const warningDismissed = ref(false);
const secondsLeft = computed(() => Math.max(0, Math.ceil((expiresAt.value - now.value) / 1000)));
const countdown = computed(() => {
    return Math.floor(secondsLeft.value / 60) + ':' + String(secondsLeft.value % 60).padStart(2, '0');
});
const showWarning = computed(() => null !== expiresAt.value && !warningDismissed.value);

let lastChecked = 0;
let checkTimer = null;
let countdownTimer = null;

const clearTimers = () => {
    clearTimeout(checkTimer);
    clearInterval(countdownTimer);
};

/**
 * Checks again shortly before they're due to be warned, as activity in another tab extends the
 * session. Once it's within the warning time, counts down & checks again when it runs out.
 */
const schedule = (remaining) => {
    clearTimers();

    // doesn't expire (remember-me)
    if (null === remaining) {
        expiresAt.value = null;

        return;
    }

    if (remaining > WARNING_SECONDS) {
        expiresAt.value = null;
        warningDismissed.value = false;
        checkTimer = setTimeout(checkSession, Math.min(remaining - WARNING_SECONDS, MAX_CHECK_INTERVAL) * 1000);

        return;
    }

    now.value = Date.now();
    expiresAt.value = now.value + remaining * 1000;
    countdownTimer = setInterval(() => {
        now.value = Date.now();

        if (0 === secondsLeft.value) {
            checkSession();
        }
    }, 1000);
};

const update = async (request) => {
    lastChecked = Date.now();

    let info;
    try {
        info = await request();
    } catch (e) {
        clearTimers();

        // the session can't be checked or extended until it's over, when it's checked again (see below)
        if (e instanceof MaintenanceError) {
            expiresAt.value = null;

            return;
        }

        logError(e);
        checkTimer = setTimeout(checkSession, RETRY_INTERVAL * 1000);

        return;
    }

    if (null === info.userId) {
        clearTimers();
        expiresAt.value = null;
        stillSignedOut.value = sessionExpired.value;
        expireSession();

        return;
    }

    // signed in as someone else: the page & any held requests belong to the previous user
    if (info.userId !== rootStore.user?.userId) {
        window.location.reload();

        return;
    }

    stillSignedOut.value = false;
    resumeSession();
    schedule(info.remaining);
};

const checkSession = () => update(fetchSessionInfo);
const keepSignedIn = () => update(extendSession);

// the session may have expired during the maintenance, as nothing could extend it
watch(maintenance, (details, previous) => {
    if (null === details && null !== previous && rootStore.loggedIn) {
        checkSession();
    }
});

// check when they come back to the window so they know before they start working
const returned = () => {
    if ('visible' !== document.visibilityState) {
        return;
    }
    // maintenance.vue checks during it & the session's checked once it's over
    if (!rootStore.loggedIn || maintenance.value) {
        return;
    }
    // while signed out, always check: they may have signed back in in another tab
    if (!sessionExpired.value && Date.now() - lastChecked < RETURN_CHECK_INTERVAL * 1000) {
        return;
    }

    checkSession();
};

onMounted(() => {
    if (rootStore.loggedIn) {
        checkSession();
    }

    window.addEventListener('focus', returned);
    document.addEventListener('visibilitychange', returned);
});
onBeforeUnmount(() => {
    clearTimers();
    window.removeEventListener('focus', returned);
    document.removeEventListener('visibilitychange', returned);
});
</script>
