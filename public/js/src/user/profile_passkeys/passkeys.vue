<template>
    <div class="form-wrap my-8 p-0">
        <ProfileTabs />

        <div class="p-4">
            <h2 class="text-xl font-semibold mb-4">Passkeys</h2>
            <p class="text-sm text-gray-600 mb-6">
                Passkeys let you sign in using your device's biometrics (Touch ID, Face ID, Windows Hello)
                without a password.
            </p>

            <div v-if="loading" class="text-center py-8">
                <LoadingSpinner />
            </div>

            <div v-else>
                <ul v-if="passkeys.length" class="divide-y divide-gray-200 mb-6">
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

                <p v-else class="text-gray-500 mb-6">You have no registered passkeys.</p>

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

                <div v-if="errorMessage" class="alert alert-danger mt-4">{{ errorMessage }}</div>
                <div v-if="successMessage" class="alert alert-success mt-4">{{ successMessage }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ProfileTabs from '@/user/profile_edit/component/tabs.vue';
import { useQuery, useMutation } from '@vue/apollo-composable';
import { logError } from '@/common/lib';
import { prepareCreationOptions, encodeCredential } from '@/common/base64url';
import { Passkeys, PasskeyDelete } from '@/user/queries/passkey.query.graphql';

const { result, loading, refetch } = useQuery(Passkeys);
const passkeys = computed(() => result.value?.Me?.passkeys ?? []);

const { mutate: deletePasskeyMutation } = useMutation(PasskeyDelete);

const passkeySupported = ref(window.PublicKeyCredential !== undefined);
const registering = ref(false);
const errorMessage = ref(null);
const successMessage = ref(null);

const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString();

const deletePasskey = async (passkeyId) => {
    if (!confirm('Are you sure you want to remove this passkey?')) {
        return;
    }

    try {
        await deletePasskeyMutation({ passkeyId });
        await refetch();
        successMessage.value = 'Passkey removed.';
        errorMessage.value = null;
    } catch (e) {
        logError(e);
        errorMessage.value = 'Failed to remove passkey. Please try again.';
    }
};

const registerPasskey = async () => {
    registering.value = true;
    errorMessage.value = null;
    successMessage.value = null;

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

        await refetch();
        successMessage.value = 'Passkey added successfully!';

    } catch (e) {
        if (e.name === 'NotAllowedError') {
            // User cancelled
            return;
        }
        logError(e);
        errorMessage.value = e.message || 'Failed to add passkey. Please try again.';
    } finally {
        registering.value = false;
    }
};
</script>
