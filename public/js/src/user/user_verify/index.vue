<template>
    <PublicWrap>
        <template #heading>Verify Account</template>
        <LoadingSpinner v-if="state.matches('verifying')">Verifying your account…</LoadingSpinner>
        <div v-if="state.matches('verified')">
            Your account has been verified. You can now sign in.
            <RouterLink :to="{ name: 'login' }" class="button w-full">Sign In</RouterLink>
        </div>
        <div v-if="state.matches('error')">
            <template v-if="invalidToken">
                <FieldError class="mb-4">
                    Your activation link is invalid.
                    Please try clicking the button again or copying the link.
                    Or you can <RouterLink :to="{ name: 'login' }">sign in</RouterLink>.
                </FieldError>
                <div class="form_button-wrap">
                    <RouterLink :to="{ name: 'login' }" class="button w-full">Sign In</RouterLink>
                </div>
            </template>
            <template v-else-if="tokenExpired">
                <FieldError class="mb-4">
                    Your verification link has expired.
                </FieldError>
                You can <RouterLink :to="{ name: 'login' }">sign in</RouterLink>
                or get a new verification link by
                <RouterLink :to="{ name: 'user-recover-initiate'}">requesting a link to reset your password</RouterLink>.
            </template>
            <FieldError v-else class="mb-4">
                There was a problem verifying your account. Please try again later.
            </FieldError>
        </div>
    </PublicWrap>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRootStore } from '@/user/stores/root';
import { useMachine } from '@xstate/vue';
import { createMachine } from 'xstate';
import { useRouter } from 'vue-router';
import { UserVerify } from '@/user/queries/user.mutation.graphql';
import { hasGraphQlError, logError } from '@/common/lib';
import { useMutation } from '@vue/apollo-composable';
import LoadingSpinner from '@/common/loading_spinner.vue';
import PublicWrap from '@/common/public_wrap.vue';

const rootStore = useRootStore();
const router = useRouter();

const stateMachine = createMachine({
    id: 'component',
    initial: 'verifying',
    states: {
        verifying: {
            on: {
                VERIFIED: 'verified',
                ERROR: 'error',
            },
        },
        verified: {
            type: 'final',
        },
        error: {
            type: 'final',
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const invalidToken = ref(false);
const tokenExpired = ref(false);

onMounted(async () => {
    if (rootStore.loggedIn) {
        router.replace({ name: 'login' });

        return;
    }

    try {
        const { mutate: sendUserVerify } = useMutation(UserVerify);
        await sendUserVerify();

        invalidToken.value = false;
        tokenExpired.value = false;

        sendEvent({ type: 'VERIFIED' });

        setTimeout(() => {
            router.push({ name: 'login' });
        }, 5000);

    } catch (e) {
        if (hasGraphQlError(e)) {
            if (404 === e.graphQLErrors[0].code) {
                invalidToken.value = true;
            } else if (405 === e.graphQLErrors[0].code) {
                tokenExpired.value = true;
            } else {
                logError(e);
            }
        } else {
            logError(e);
        }

        sendEvent({ type: 'ERROR' });
        window.scrollTo(0, 0);
    }
});
</script>
