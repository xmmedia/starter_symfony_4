<template>
    <div ref="wrapper">
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

    <Transition name="button_bar">
        <div v-if="edited && showSaveBar" class="button_bar">
            <button v-if="showButton"
                    type="submit"
                    class="button"
                    :disabled="isSaving || isSaved || disableButton">
                <slot>Save</slot>
            </button>
            <slot v-if="!isSaving && !isSaved" name="cancel">
                <RouterLink v-if="cancelTo" :to="cancelTo" class="form-action">
                    <slot name="cancel-text">Cancel</slot>
                </RouterLink>
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
    </Transition>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
    edited: {
        type: Boolean,
        default: false,
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

const wrapper = ref();
const showSaveBar = ref(true);

const observer = new IntersectionObserver((entries) => {
    entries.forEach(({ isIntersecting }) => {
        showSaveBar.value = !isIntersecting;
    });
}, { rootMargin: "0px 0px 10px 0px" });
onMounted(() => {
    observer.observe(wrapper.value);
});
onBeforeUnmount(() => {
    observer.disconnect();
});

const isSaving = computed(() => props.saving || 'saving' === props.status);
const isSaved = computed(() => props.saved || 'saved' === props.status);
</script>
