<template>
    <div>
        <button v-if="showButton"
                type="submit"
                class="button"
                :disabled="isSaving || isSaved">
            <slot>Save</slot>
        </button>
        <slot name="cancel">
            <router-link v-if="cancelTo"
                         :to="cancelTo"
                         class="form-action">Cancel</router-link>
        </slot>

        <span v-if="isSaving" class="ml-4 text-sm italic">
            <span class="loading" aria-hidden="true" />
            <slot name="saving">Saving...</slot>
        </span>
        <span v-else-if="isSaved" class="ml-4 text-sm italic">
            <slot name="saved">Saved</slot>
        </span>
    </div>
</template>

<script>
export default {
    props: {
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
            type: Object,
            default: null,
        },
        showButton: {
            type: Boolean,
            default: true,
        },
    },

    computed: {
        isSaving () {
            return this.saving || this.status === 'saving';
        },
        isSaved () {
            return this.saved || this.status === 'saved';
        },
    },
};
</script>
