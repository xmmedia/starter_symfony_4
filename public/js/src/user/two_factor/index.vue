<template>
    <PublicFormWrap>
        <template #heading>Two-Factor Authentication</template>

        <p class="text-sm text-gray-600 mb-6">
            Enter the 6-digit code from your authenticator app.
        </p>

        <form method="post" action="/2fa_check" @submit.prevent="submit">
            <div class="field-wrap">
                <label for="authCode">Authenticator code</label>
                <input id="authCode"
                       v-model="code"
                       type="text"
                       name="_auth_code"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       maxlength="6"
                       pattern="[0-9]{6}"
                       required
                       autofocus
                       @input="autoSubmit">
            </div>

            <div v-if="errorMessage" class="alert alert-warning mb-4">{{ errorMessage }}</div>

            <button type="submit" class="button w-full" :disabled="submitting">
                <LoadingSpinner v-if="submitting" spinner-classes="bg-white" />
                <template v-else>Verify</template>
            </button>
        </form>
    </PublicFormWrap>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useHead } from '@unhead/vue';
import PublicFormWrap from '@/common/public_form_wrap.vue';

useHead({
    title: 'Two-Factor Authentication',
});

const code = ref('');
const submitting = ref(false);
const errorMessage = ref(null);

onMounted(() => {
    // Check for error from server (injected via page data or query param)
    const params = new URLSearchParams(window.location.search);
    if (params.has('error')) {
        errorMessage.value = 'Invalid code. Please try again.';
    }
});

const autoSubmit = () => {
    if (code.value.replace(/\s/g, '').length === 6) {
        submit();
    }
};

const submit = () => {
    if (submitting.value) {
        return;
    }
    submitting.value = true;

    // Submit as a standard form POST to /2fa_check
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/2fa_check';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_auth_code';
    input.value = code.value.replace(/\s/g, '');
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
};
</script>
