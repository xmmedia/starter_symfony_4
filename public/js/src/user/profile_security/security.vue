<template>
    <div class="form-wrap my-8 p-0">
        <ProfileTabs />

        <div class="p-4">
            <!-- Passkeys -->
            <h2 class="mt-0">Passkeys</h2>
            <p class="text-sm text-gray-600 mb-4">
                Sign in using your device's biometrics (Touch ID, Face ID, Windows Hello) without a password.
            </p>

            <div v-if="passkeysLoading" class="text-center py-4">
                <LoadingSpinner />
            </div>

            <div v-else>
                <ul v-if="passkeys.length" class="divide-y divide-gray-200 mb-4">
                    <li v-for="passkey in passkeys"
                        :key="passkey.passkeyId"
                        class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ passkey.name || 'Passkey' }}</p>
                            <p class="text-xs text-gray-500">
                                Added {{ formatDate(passkey.createdAt) }}
                                <template v-if="passkey.lastUsedAt">
                                    · Last used {{ formatDate(passkey.lastUsedAt) }}
                                </template>
                            </p>
                        </div>
                        <button type="button"
                                class="button-link text-red-600 text-sm"
                                @click="deletePasskey(passkey.passkeyId)">
                            Remove
                        </button>
                    </li>
                </ul>

                <p v-else class="text-gray-500 mb-4">You have no registered passkeys.</p>

                <div v-if="!passkeySupported" class="alert alert-warning mb-4">
                    Your browser does not support passkeys.
                </div>

                <button v-else
                        type="button"
                        class="button"
                        :disabled="registering"
                        @click="registerPasskey">
                    <LoadingSpinner v-if="registering" />
                    <template v-else>Add a passkey</template>
                </button>

                <div v-if="passkeyError" class="alert alert-danger mt-4">{{ passkeyError }}</div>
                <div v-if="passkeySuccess" class="alert alert-success mt-4">{{ passkeySuccess }}</div>
            </div>

            <hr class="my-6 border-gray-300">

            <!-- Two-Factor Authentication -->
            <h2>Two-Factor Authentication</h2>

            <div v-if="totpLoading" class="text-center py-4">
                <LoadingSpinner />
            </div>

            <template v-else>
                <!-- 2FA is enabled -->
                <template v-if="twoFactorEnabled && !state.matches('disabling')">
                    <div class="alert alert-success mb-4">
                        <span>
                            Two-factor authentication is <strong>enabled</strong>.
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">
                        You'll need your authenticator app each time you sign in with a password.
                    </p>

                    <button type="button"
                            class="button bg-red-600 border-red-600"
                            @click="sendEvent({ type: 'DISABLE' })">
                        Disable 2FA
                    </button>
                </template>

                <!-- Disable form -->
                <template v-if="state.matches('disabling')">
                    <p class="mb-4 text-sm">Enter your current authenticator code to disable 2FA:</p>
                    <form @submit.prevent="submitDisable">
                        <div class="field-wrap">
                            <label for="disableCode">Authenticator code</label>
                            <input id="disableCode"
                                   v-model="code"
                                   type="text"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   maxlength="6"
                                   pattern="[0-9]*"
                                   required
                                   autofocus>
                        </div>
                        <div v-if="totpError" class="alert alert-danger mb-4">{{ totpError }}</div>
                        <div class="flex gap-x-2">
                            <button type="submit"
                                    class="button bg-red-600 border-red-600"
                                    :disabled="state.matches('saving')">
                                <LoadingSpinner v-if="state.matches('saving')" />
                                <template v-else>Confirm disable</template>
                            </button>
                            <button type="button"
                                    class="button-link"
                                    @click="sendEvent({ type: 'CANCEL' })">Cancel</button>
                        </div>
                    </form>
                </template>

                <!-- 2FA is disabled -->
                <!-- eslint-disable-next-line max-len -->
                <template v-if="!twoFactorEnabled && !state.matches('setup') && !state.matches('confirming') && !state.matches('saving')">
                    <p class="text-sm text-gray-600 mb-4">
                        Add an extra layer of security. Use an authenticator app like
                        Google Authenticator, Authy, or 1Password to generate verification codes.
                    </p>
                    <button type="button" class="button" @click="startSetup">
                        Enable 2FA
                    </button>
                </template>

                <!-- Setup step 1: show QR code -->
                <template v-if="state.matches('setup')">
                    <p class="mb-4 text-sm">
                        Scan this QR code with your authenticator app, then enter the 6-digit code below.
                    </p>
                    <div class="mb-4 flex justify-center">
                        <canvas ref="qrCanvas" />
                    </div>
                    <details class="text-sm mb-4">
                        <summary class="cursor-pointer text-gray-600">Can't scan? Enter the key manually</summary>
                        <div class="flex items-start gap-x-2 mt-2">
                            <code class="break-all">{{ manualKey }}</code>
                            <CopyButton :text="manualKey" title="Copy key" icon-component="PublicIcon" />
                        </div>
                    </details>

                    <form @submit.prevent="submitConfirm">
                        <div class="field-wrap">
                            <label for="confirmCode">Authenticator code</label>
                            <input id="confirmCode"
                                   v-model="code"
                                   type="text"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   maxlength="6"
                                   pattern="[0-9]*"
                                   required
                                   autofocus>
                        </div>
                        <div v-if="totpError" class="alert alert-danger mb-4">{{ totpError }}</div>
                        <div class="flex gap-x-2">
                            <button type="submit"
                                    class="button"
                                    :disabled="state.matches('confirming')">
                                <LoadingSpinner v-if="state.matches('confirming')" />
                                <template v-else>Verify and enable</template>
                            </button>
                            <button type="button"
                                    class="button-link"
                                    @click="sendEvent({ type: 'CANCEL' })">Cancel</button>
                        </div>
                    </form>
                </template>

                <div v-if="state.matches('done')" class="alert alert-success mt-4">
                    Two-factor authentication has been {{ lastAction }}.
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import ProfileTabs from '@/user/profile_edit/component/tabs.vue';
import CopyButton from '@/common/copy.vue';
import { useQuery, useMutation } from '@vue/apollo-composable';
import { logError } from '@/common/lib';
import { createMachine } from 'xstate';
import { useMachine } from '@xstate/vue';
import { prepareCreationOptions, encodeCredential } from '@/common/base64url';
import { MeQuery } from '@/user/queries/user.query.graphql';
import { Passkeys, PasskeyDelete, UserRequestTotpSetup, UserConfirmTotpSetup, UserDisableTotp } from '@/user/queries/passkey.query.graphql';

// Passkeys
const { result: passkeysResult, loading: passkeysLoading, refetch: refetchPasskeys } = useQuery(Passkeys);
const passkeys = computed(() => passkeysResult.value?.Me?.passkeys ?? []);
const { mutate: deletePasskeyMutation } = useMutation(PasskeyDelete);

const passkeySupported = ref(window.PublicKeyCredential !== undefined);
const registering = ref(false);
const passkeyError = ref(null);
const passkeySuccess = ref(null);

const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString();

const deletePasskey = async (passkeyId) => {
    if (!confirm('Are you sure you want to remove this passkey?')) {
        return;
    }

    try {
        await deletePasskeyMutation({ passkeyId });
        await refetchPasskeys();
        passkeySuccess.value = 'Passkey removed.';
        passkeyError.value = null;
    } catch (e) {
        logError(e);
        passkeyError.value = 'Failed to remove passkey. Please try again.';
    }
};

const registerPasskey = async () => {
    registering.value = true;
    passkeyError.value = null;
    passkeySuccess.value = null;

    try {
        const name = prompt('Name this passkey (optional):', 'My device') || null;

        const optionsRes = await fetch('/passkey/register/options', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({}),
        });

        if (!optionsRes.ok) {
            throw new Error('Failed to get registration options.');
        }

        const options = await optionsRes.json();
        const credential = await navigator.credentials.create({
            publicKey: prepareCreationOptions(options),
        });

        const registerRes = await fetch('/passkey/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...encodeCredential(credential), name }),
        });

        const data = await registerRes.json();
        if (!data.success) {
            throw new Error(data.error || 'Registration failed.');
        }

        await refetchPasskeys();
        passkeySuccess.value = 'Passkey added successfully!';

    } catch (e) {
        if (e.name === 'NotAllowedError') {
            return;
        }
        logError(e);
        passkeyError.value = e.message || 'Failed to add passkey. Please try again.';
    } finally {
        registering.value = false;
    }
};

// TOTP 2FA
const { result: totpResult, loading: totpLoading, refetch: refetchTotp } = useQuery(MeQuery);
const twoFactorEnabled = computed(() => totpResult.value?.Me?.twoFactorEnabled ?? false);

const { mutate: requestSetup } = useMutation(UserRequestTotpSetup);
const { mutate: confirmSetup } = useMutation(UserConfirmTotpSetup);
const { mutate: disableTotp } = useMutation(UserDisableTotp);

const stateMachine = createMachine({
    id: 'totp',
    initial: 'idle',
    states: {
        idle: {
            on: { SETUP: 'setup', DISABLE: 'disabling' },
        },
        setup: {
            on: { CONFIRM: 'confirming', CANCEL: 'idle' },
        },
        confirming: {
            on: { DONE: 'done', ERROR: 'setup' },
        },
        disabling: {
            on: { SAVE: 'saving', CANCEL: 'idle' },
        },
        saving: {
            on: { DONE: 'done', ERROR: 'disabling' },
        },
        done: {
            on: { RESET: 'idle' },
        },
    },
});
const { snapshot: state, send: sendEvent } = useMachine(stateMachine);

const code = ref('');
const totpError = ref(null);
const manualKey = ref(null);
const lastAction = ref('');
const qrCanvas = ref(null);

const startSetup = async () => {
    totpError.value = null;

    try {
        const { data } = await requestSetup();
        manualKey.value = data.UserRequestTotpSetup.manualKey;
        sendEvent({ type: 'SETUP' });

        await nextTick();
        const QRCode = (await import('qrcode')).default;
        await QRCode.toCanvas(qrCanvas.value, data.UserRequestTotpSetup.otpauthUrl, { width: 200 });
    } catch (e) {
        logError(e);
        totpError.value = 'Failed to start 2FA setup. Please try again.';
    }
};

const submitConfirm = async () => {
    sendEvent({ type: 'CONFIRM' });
    totpError.value = null;

    try {
        await confirmSetup({ code: code.value });
        lastAction.value = 'enabled';
        await refetchTotp();
        sendEvent({ type: 'DONE' });
        code.value = '';
        setTimeout(() => sendEvent({ type: 'RESET' }), 3000);
    } catch (e) {
        logError(e);
        totpError.value = e.message?.includes('Invalid') ? 'Invalid code. Please try again.' : 'Failed to enable 2FA.';
        sendEvent({ type: 'ERROR' });
    }
};

const submitDisable = async () => {
    sendEvent({ type: 'SAVE' });
    totpError.value = null;

    try {
        await disableTotp({ code: code.value });
        lastAction.value = 'disabled';
        await refetchTotp();
        sendEvent({ type: 'DONE' });
        code.value = '';
        setTimeout(() => sendEvent({ type: 'RESET' }), 3000);
    } catch (e) {
        logError(e);
        totpError.value = e.message?.includes('Invalid') ? 'Invalid code. Please try again.' : 'Failed to disable 2FA.';
        sendEvent({ type: 'ERROR' });
    }
};
</script>
