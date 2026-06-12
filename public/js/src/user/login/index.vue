<template>
    <PublicFormWrap>
        <template #heading>Sign In</template>

        <PublicAlert v-if="lastErrorMessage" ref="alertEl" class="alert-warning">{{ lastErrorMessage }}</PublicAlert>

        <form v-if="state.matches('ready')" method="post" @submit.prevent="submitStep1">
            <!-- field names match what Symfony uses by default -->
            <div class="field-wrap">
                <label for="inputEmail">Email</label>
                <input id="inputEmail"
                       v-model="email"
                       type="email"
                       name="_username"
                       required
                       autofocus
                       autocomplete="username email"
                       placeholder="julie@example.com">
            </div>

            <button type="submit"
                    class="button w-full mt-2"
                    :disabled="!emailIsValid"
                    :title="!emailIsValid ? 'Enter your email to continue' : null">
                Continue
            </button>

            <div v-if="passkeySupported" class="flex items-center justify-center gap-x-4 my-6 text-fs-gray-500">
                <hr class="grow">
                Or
                <hr class="grow">
            </div>

            <button v-if="passkeySupported"
                    type="button"
                    class="button w-full bg-fs-gray-300 border-fs-gray-300"
                    :disabled="state.matches('passkey')"
                    @click="signInWithPasskey(null)">
                <LoadingSpinner v-if="state.matches('passkey')" spinner-classes="bg-white" />
                <template v-else>Sign in with a passkey</template>
            </button>
        </form>

        <!-- posts back the current url -->
        <form v-if="showPasswordForm" ref="passwordFormEl" method="post">
            <div class="mb-4 text-lg font-semibold">
                <button type="button"
                        class="flex items-center gap-x-1"
                        title="Change email"
                        @click="sendEvent({ type: 'BACK'})">
                    <PublicIcon icon="arrow-section" width="12" height="12" class="rotate-90" />
                    Sign in with {{ email }}
                </button>
            </div>

            <!-- field names match what Symfony uses by default -->
            <input type="hidden" name="_username" :value="email">

            <FieldPassword id="inputPassword"
                           v-model="password"
                           autofocus
                           name="_password"
                           autocomplete="current-password"
                           icon-component="PublicIcon" />

            <div class="field-wrap field-wrap-checkbox">
                <input id="rememberMe" type="checkbox" name="_remember_me" value="on">
                <label for="rememberMe">Remember me</label>
            </div>

            <button type="submit" class="button w-full">Sign in</button>

            <div class="flex items-center justify-center gap-x-4 my-6 text-fs-gray-500">
                <hr class="grow">
                Or
                <hr class="grow">
            </div>

            <button type="button" class="button w-full bg-fs-gray-300 border-fs-gray-300" @click="sendLoginLink">
                <LoadingSpinner v-if="state.matches('sending')" spinner-classes="bg-white" />
                <template v-else>Sign in with a link instead</template>
            </button>

            <p class="text-center text-sm">Go passwordless! We'll send you an email.</p>

            <button v-if="passkeySupported"
                    type="button"
                    class="button w-full bg-fs-gray-300 border-fs-gray-300 mt-2"
                    :disabled="state.matches('passkey')"
                    @click="signInWithPasskey(email)">
                <LoadingSpinner v-if="state.matches('passkey')" spinner-classes="bg-white" />
                <template v-else>Sign in with a passkey instead</template>
            </button>

            <RouterLink :to="{ name: 'user-recover-initiate', query: { email } }" class="block mt-8 text-sm">
                Forgot your password?
            </RouterLink>
        </form>

        <div v-if="state.matches('sent')" class="alert alert-success">
            <div>A magic link has been sent to your email. Click the link in the email to access your account.</div>
            <button type="button"
                    class="button-link pl-4 flex items-center gap-x-1 fill-current"
                    @click="sendEvent({ type: 'BACK' })">
                <PublicIcon icon="arrow-section" width="12" height="12" class="rotate-90" /> Back
            </button>
        </div>

    </PublicFormWrap>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useHead } from '@unhead/vue';
import { useRoute, useRouter } from 'vue-router';
import { useMutation, useQuery } from '@vue/apollo-composable';
import { useRootStore } from '@/user/stores/root';
import { logError } from '@/common/lib';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { AuthLast } from '@/user/queries/auth.query.graphql';
import { UserLoginLink } from '@/user/queries/user.mutation.graphql';
import FieldPassword from '@/common/field_password.vue';
import PublicFormWrap from '@/common/public_form_wrap.vue';
import PublicAlert from '@/common/public_alert.vue';
import { email as emailValidator } from '@vuelidate/validators';
import { prepareRequestOptions, encodeCredential } from '@/common/base64url';

const router = useRouter();
const route = useRoute();
const rootStore = useRootStore();

const stateMachine = createMachine({
    id: 'component',
    initial: 'ready',
    states: {
        ready: {
            on: {
                NEXT:    'step2',
                PASSKEY: 'passkey',
            },
        },
        step2: {
            on: {
                SEND:    'sending',
                BACK:    'ready',
                PASSKEY: 'passkey',
            },
        },
        sending: {
            on: {
                SENT:  'sent',
                ERROR: 'ready',
            },
        },
        sent: {
            on: {
                BACK: 'step2',
            },
        },
        passkey: {
            on: {
                ERROR: 'ready',
                STEP2: 'step2',
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
const lastErrorMessage = ref(null);
const alertEl = ref();
const passwordFormEl = ref();

const passkeySupported = computed(() => window.PublicKeyCredential !== undefined);

const emailIsValid = computed(() => email.value && emailValidator.$validator(email.value));
const showPasswordForm = computed(() => state.value.matches('step2') || state.value.matches('sending'));

const { onResult } = useQuery(AuthLast);
onResult(({ data: { AuthLast } }) => {
    if (AuthLast.email) {
        email.value = AuthLast.email;
        submitStep1();
    }

    if (AuthLast.error) {
        lastErrorMessage.value = AuthLast.error;
        nextTick(() => {
            alertEl.value.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
});

onMounted(() => {
    if (rootStore.loggedIn) {
        window.location = router.resolve({ name: 'login' }).href;

        return;
    }

    if (route.query.email) {
        email.value = route.query.email;

        submitStep1();
    }
});

const submitStep1 = () => {
    sendEvent({ type: 'NEXT' });
    nextTick(() => {
        if (!lastErrorMessage.value) {
            passwordFormEl.value.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
};

const signInWithPasskey = async (username) => {
    sendEvent({ type: 'PASSKEY' });
    lastErrorMessage.value = null;

    try {
        const body = username ? JSON.stringify({ username }) : JSON.stringify({});
        const optionsRes = await fetch('/passkey/login/options', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
        });

        if (!optionsRes.ok) {
            throw new Error('Failed to get passkey options.');
        }

        const options = await optionsRes.json();
        const credential = await navigator.credentials.get({
            publicKey: prepareRequestOptions(options),
        });

        const loginRes = await fetch('/passkey/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(encodeCredential(credential)),
        });

        const data = await loginRes.json();
        if (data.redirect) {
            window.location.href = data.redirect;

            return;
        }

        throw new Error(data.error || 'Passkey authentication failed.');

    } catch (e) {
        if (e.name === 'NotAllowedError') {
            // User cancelled or dismissed the prompt — go back to form
            sendEvent({ type: email.value ? 'STEP2' : 'ERROR' });

            return;
        }
        logError(e);
        lastErrorMessage.value = e.message || 'Passkey sign-in failed. Please try another method.';
        sendEvent({ type: 'ERROR' });
    }
};

const sendLoginLink = async () => {
    if (!state.value.matches('step2')) {
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
};
</script>
