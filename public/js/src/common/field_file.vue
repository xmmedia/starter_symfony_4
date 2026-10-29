<template>
    <div class="field-wrap">
        <slot :id="id" name="label">
            <label :for="id"><slot></slot></label>
        </slot>

        <FieldError v-if="v" :v="v">
            <template #required><slot name="required"></slot></template>
            <template #valid><slot name="valid"></slot></template>
        </FieldError>
        <ul v-if="tooSmall" class="field-errors" role="alert">
            <li>The file is too small. Check it's the right file.</li>
        </ul>

        <input :id="id"
               ref="input"
               type="file"
               :accept="accept"
               :class="inputClasses"
               @change="fileSelected">
        <div v-if="!!$slots.help" class="field-help"><slot name="help"></slot></div>

        <div v-if="file" class="mt-2">
            <template v-if="preview">
                <img v-if="file instanceof UploadableFile && file.isImage"
                     :src="file.url"
                     class="w-50 border border-gray-300"
                     alt="">
                <template v-else-if="!(file instanceof UploadableFile) && file.file">
                    <div>Existing:</div>
                    <img v-if="!existingImageError"
                         :src="file.file"
                         class="w-50 border border-gray-300"
                         alt="Existing image"
                         @error="existingImageError = true">
                    <div v-else class="text-sm text-gray-500 italic">Image missing</div>
                </template>
            </template>
            <!-- an existing file that's already saved -->
            <a v-else-if="!(file instanceof UploadableFile) && file.file" :href="file.file" target="_blank">
                {{ file.originalFilename ?? 'Existing file' }}
            </a>

            <button v-if="removable" class="button-link block text-sm" type="button" @click="remove">
                × Remove
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, useTemplateRef, watch } from 'vue';
import { createId } from '@paralleldrive/cuid2';
import { UploadableFile } from '@/common/classes/uploadableFile';

// an UploadableFile once one's picked, or an existing file ({ file: url, originalFilename })
const file = defineModel({ type: Object, default: null });

const props = defineProps({
    // eg ".pdf,application/pdf"
    accept: {
        type: String,
        default: null,
    },
    // in bytes: a smaller file is likely empty or not the one they meant
    minSize: {
        type: Number,
        default: 1,
    },
    // show an image's preview
    preview: {
        type: Boolean,
        default: false,
    },
    // false where there's nothing to remove it from, eg a file that's only read, not saved
    removable: {
        type: Boolean,
        default: true,
    },
    inputClasses: {
        type: [ String, Array, Object ],
        default: null,
    },
    v: {
        type: Object,
        default: null,
    },
    id: {
        type: String,
        default: () => createId(),
    },
});

const input = useTemplateRef('input');
const tooSmall = ref(false);
const existingImageError = ref(false);

// the input's cleared when the file is, eg when the form's reset
watch(file, (value) => {
    existingImageError.value = false;
    if (null === value && input.value) {
        input.value.value = '';
    }
});

const fileSelected = (event) => {
    const files = event.target.files;
    tooSmall.value = false;
    if (!files.length) {
        return;
    }

    if (files[0].size < props.minSize) {
        tooSmall.value = true;
        event.target.value = '';
        file.value = null;

        return;
    }

    file.value = new UploadableFile(files[0]);
};

const remove = () => {
    tooSmall.value = false;
    file.value = null;
};
</script>
