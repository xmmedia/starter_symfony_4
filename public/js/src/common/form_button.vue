<template>
    <div ref="wrapper" :class="wrapperClasses">
        <div :class="buttonWrapperClasses ? buttonWrapperClasses : 'contents'">
            <button v-if="showButton"
                    type="submit"
                    class="button"
                    :class="buttonClasses"
                    :disabled="isSaving || isSaved || disableButton">
                <slot>Save</slot>
            </button>
        </div>
        <slot name="before"></slot>

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

    <Transition name="form-button_bar">
        <div v-if="edited && showButtonBar" class="form-button_bar">
            <slot name="before"></slot>

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
import { onBeforeRouteLeave } from 'vue-router';

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
    buttonClasses: {
        type: [String, Array],
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
    wrapperClasses: {
        type: [String, Array],
        default: null,
    },
    /**
     * Wraps the before slot and main button.
     * Doesn't apply to the button bar.
     */
    buttonWrapperClasses: {
        type: [String, Array],
        default: null,
    },
});

const wrapper = ref();
const showButtonBar = ref(true);

const observer = new IntersectionObserver((entries) => {
    entries.forEach(({ isIntersecting }) => {
        showButtonBar.value = !isIntersecting;
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

onBeforeRouteLeave((to, from, next) => {
    const msg = 'Are you sure you want to leave? You have unsaved changes.';
    if (props.edited && !isSaved.value && !confirm(msg)) {
        return next(false);
    }

    next();
});

const unload = (e) => {
    if (props.edited && !isSaved.value) {
        e.preventDefault();
    }
};

window.addEventListener('beforeunload', unload);
onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', unload);
});
</script>
