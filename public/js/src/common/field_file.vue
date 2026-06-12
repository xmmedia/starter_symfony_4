<template>
    <div class="field-wrap">
        <label :for="id"><slot></slot></label>

        <FieldError v-if="v$.value.$error" :v="v$.value" />

        <input :id="id"
               ref="input"
               type="file"
               :accept="accept"
               @change="fileSelected($event)">
        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>

        <div v-if="value" class="my-4 ml-4">
            <img v-if="value instanceof UploadableFile" :src="value.url" class="w-50 border border-gray-300" alt="">
            <div v-else-if="value?.file">
                <div>Existing:</div>
                <img v-if="!existingImageError"
                     :src="value.file"
                     class="w-50 border border-gray-300"
                     alt="Existing image"
                     @error="existingImageError = true">
                <div v-else class="text-sm text-gray-500 italic">Image missing</div>
            </div>

            <button class="button-link block text-sm" type="button" @click="removeImage">
                × Remove
            </button>
        </div>
    </div>
</template>

<script setup>
import { UploadableFile } from '@/common/classes/uploadableFile';
import { useVuelidate } from '@vuelidate/core';
import { requiredIf } from '@vuelidate/validators';
import { ref, useTemplateRef, watch } from 'vue';
import cuid from 'cuid';

const value = defineModel({ type: [ UploadableFile, Object ] });

const fileInput = useTemplateRef('input');
const existingImageError = ref(false);

watch(() => value.value?.file, () => { existingImageError.value = false; });

const props = defineProps({
    required: {
        type: Boolean,
        default: false,
    },
    accept: {
        type: String,
        default: null,
    },
    id: {
        type: String,
        default: () => cuid(),
    },
});

const v$ = useVuelidate({
    value: {
        required: requiredIf(() => props.required),
    },
}, { value });

const fileSelected = (event) => {
    const files = event.target.files;
    if (!files.length) {
        return;
    }

    if (files[0].size <= 100) {
        alert('File size must be greater than 100 bytes.');
        return;
    }

    value.value = new UploadableFile(files[0]);
};

const removeImage = () => {
    value.value = null;
    fileInput.value.value = null;
};
</script>
