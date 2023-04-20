<template>
    <div>
        <button v-if="showButton"
                type="submit"
                class="button"
                :disabled="isSaving || isSaved || disableButton">
            <slot>Save</slot>
        </button>
        <slot v-if="!isSaving && !isSaved" name="cancel">
            <RouterLink v-if="cancelTo"
                        :to="cancelTo"
                        class="form-action"><slot name="cancel-text">Cancel</slot></RouterLink>
        </slot>
        <slot v-if="!isSaving && !isSaved" name="additional"></slot>

        <span v-if="isSaving" class="ml-4 text-sm italic">
            <span class="loading" aria-hidden="true" />
            <slot name="saving">Saving…</slot>
        </span>
        <span v-else-if="isSaved" class="ml-4 text-sm italic">
            <slot name="saved">Saved</slot>
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    saved: {
        type: Boolean,
        default: false,
    },
    cancelTo: {
        type: [Object, String],
        default: null,
    },
    showButton: {
        type: Boolean,
        default: true,
    },
    disableButton: {
        type: Boolean,
        default: false,
    },
});

const isSaving = computed(() => props.saving || 'saving' === props.status);
const isSaved = computed(() => props.saved || 'saved' === props.status);
</script>
