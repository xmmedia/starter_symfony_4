<template>
    <div>
        <div class="form-wrap">
            <h1 class="mt-0 leading-none">Sign In</h1>

            <div v-if="errorMsg" class="alert alert-warning">{{ errorMsg }}</div>

            <!-- posts back the current url -->
            <form v-if="!magicLink" method="post">
                <!-- field names match what Symfony uses by default -->
                <div class="field-wrap">
                    <label for="inputEmail">Email address</label>
                    <input id="inputEmail"
                           ref="passwordEmailInput"
                           v-model="email"
                           type="email"
                           name="_username"
                           required
                           autofocus
                           autocomplete="username email">
                </div>

                <FieldPassword id="inputPassword"
                               v-model="password"
                               name="_password"
                               autocomplete="current-password"
                               icon-component="PublicIcon" />

                <div class="field-wrap field-wrap-checkbox">
                    <input id="rememberMe"
                           type="checkbox"
                           name="_remember_me"
                           value="on">
                    <label for="rememberMe">Remember me</label>
                </div>

                <div class="flex justify-between">
                    <div>
                        <button type="submit" class="button">Sign In</button>
                        <RouterLink :to="{ name: 'user-recover-initiate' }" class="form-action">
                            Forgot your password?
                        </RouterLink>
                    </div>
                    <button type="button"
                            class="button-link form-action"
                            @click="showLink">Get magic login link</button>
                </div>
            </form>

            <!-- magic link form -->
            <div v-else>
                <form v-if="state.matches('ready') || state.matches('sending')"
                      method="post"
                      @submit.prevent="sendLoginLink">
                    <!-- field names match what Symfony uses by default -->
                    <div class="field-wrap">
                        <label for="inputEmail">Email address</label>
                        <input id="inputEmail"
                               ref="linkEmailInput"
                               v-model="email"
                               type="email"
                               name="_username"
                               required
                               autofocus
                               autocomplete="username email">
                    </div>

                    <div class="flex justify-between">
                        <div class="flex gap-x-4">
                            <button type="submit" class="button">Get Link</button>
                            <LoadingSpinner v-if="state.matches('sending')"
                                            class="text-sm italic">Sending…</LoadingSpinner>
                        </div>
                        <button type="button"
                                class="button-link form-action"
                                @click="showLogin">Sign in with password</button>
                    </div>
                </form>
                <div v-if="state.matches('sent')">
                    <p>A magic login link has been sent to your email address.<br>
                        Please check your email and click the link to sign in.</p>
                    <div class="mt-4">
                        <button type="button"
                                class="button-link text-sm"
                                @click="showLogin">Sign in with password</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useHead } from '@unhead/vue';
import { useRoute, useRouter } from 'vue-router';
import { useMutation, useQuery } from '@vue/apollo-composable';
import { useRootStore } from '@/user/stores/root';
import { logError } from '@/common/lib.js';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { AuthLast } from '@/user/queries/auth.query.graphql';
import { UserLoginLink } from '@/user/queries/user.mutation.graphql';
import LoadingSpinner from '@/common/loading_spinner.vue';
import FieldPassword from '@/common/field_password.vue';

const rootStore = useRootStore();
const router = useRouter();
const route = useRoute();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                SEND: 'sending',
            },
        },
        sending: {
            on: {
                SENT: 'sent',
                ERROR: 'ready',
            },
        },
        sent: {
            on: {
                RESET: 'ready',
            },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

useHead({
    title: 'Sign In',
});

const email = ref(null);
const password = ref(null);
const errorMsg = ref(null);
const magicLink = ref(false);
const passwordEmailInput = ref(null);
const linkEmailInput = ref(null);

const { onResult } = useQuery(AuthLast);
onResult(({ data: { AuthLast }}) => {
    if (!email.value) {
        email.value = AuthLast.email ?? null;
    }
    if (AuthLast.error) {
        errorMsg.value = AuthLast.error ?? null;
    }
});

onMounted(() => {
    if (rootStore.loggedIn) {
        window.location = router.resolve({ name: 'login' }).href;

        return;
    }

    if (route.query.magic) {
        showLink();
    }

    if (route.query.email) {
        email.value = route.query.email;
    }
});

const showLogin = () => {
    magicLink.value = false;
    sendEvent({ type: 'RESET' });
    nextTick(() => {
        passwordEmailInput.value.focus();
    });
};
const showLink = () => {
    magicLink.value = true;
    errorMsg.value = null;
    nextTick(() => {
        linkEmailInput.value.focus();
    });
};

async function sendLoginLink () {
    if (!state.value.matches('ready')) {
        return;
    }

    sendEvent({ type: 'SEND' });

    try {
        const { mutate: sendUserLoginLink } = useMutation(UserLoginLink);
        await sendUserLoginLink({
            email: email.value,
        });

        sendEvent({ type: 'SENT' });

    } catch (e) {
        logError(e);
        alert('There was a problem send the magic link. Please try again later or login with your password.');
        sendEvent({ type: 'ERROR' });
    }
}
</script>
